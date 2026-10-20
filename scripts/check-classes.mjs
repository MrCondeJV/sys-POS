#!/usr/bin/env node
/**
 * scripts/check-classes.mjs
 * 1. Extrae TODAS las clases CSS/Tailwind usadas en resources/views (.blade.php)
 *    y verifica que cada utilidad exista en el CSS compilado de public/build.
 * 2. Lista los alias temporales (.card-banking, .btn-*-banking, .input-base,
 *    .input-banking, .input-select, .table-banking, .table-corporate, .kpi-card,
 *    .bg-theme-primary, .text-theme-primary, .border-theme-primary) aún en uso
 *    con conteo por archivo.
 * 3. Detecta y marca clases dinámicas concatenadas con interpolación Blade
 *    (ej. sm:grid-cols-{{ ... }}, bg-{{ $color }}-500) que Tailwind no puede escanear.
 */

import fs from 'node:fs';
import path from 'node:path';

const ROOT = process.cwd();
const VIEWS_DIR = path.join(ROOT, 'resources', 'views');
const MANIFEST_PATH = path.join(ROOT, 'public', 'build', 'manifest.json');

// Clases marcadoras (Tailwind/Alpine/DOM) que se usan como selectores padre o hooks JS
const MARKER_CLASSES = new Set([
    'group',
    'peer',
    'dark',
    'light',
    'prose',
    'not-prose',
    'no-print',
    'pos-sidebar',
]);

// Alias temporales que deben retirarse progresivamente en Fase 4
const TEMPORARY_ALIASES = [
    'card-banking',
    'btn-primary-banking',
    'btn-secondary-banking',
    'btn-outline-banking',
    'input-banking',
    'input-base',
    'input-select',
    'table-banking',
    'table-corporate',
    'kpi-card',
    'bg-theme-primary',
    'text-theme-primary',
    'border-theme-primary',
];

function walkBladeFiles(dir, fileList = []) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            walkBladeFiles(fullPath, fileList);
        } else if (entry.isFile() && entry.name.endsWith('.blade.php')) {
            fileList.push(fullPath);
        }
    }
    return fileList;
}

/**
 * Escapa un nombre de clase tal como lo serializa Tailwind CSS v4 en el archivo CSS compilado
 */
function toCssEscapedSelector(cls) {
    return '.' + cls.replace(/([:/[\].%#!(),'&+$^*~=@<>?`|\\])/g, '\\$1');
}

const VALID_FOLLOW_CHARS = new Set([' ', '\t', '\n', '\r', ',', '{', ':', '.', '[', '>', '+', '~', ')']);

function classExistsInCss(cls, cssContent, localStyleContent = '') {
    if (MARKER_CLASSES.has(cls)) return true;

    const target = toCssEscapedSelector(cls);
    for (const source of [cssContent, localStyleContent]) {
        if (!source) continue;
        let idx = source.indexOf(target);
        while (idx !== -1) {
            const nextChar = source[idx + target.length];
            if (nextChar === undefined || VALID_FOLLOW_CHARS.has(nextChar)) {
                return true;
            }
            idx = source.indexOf(target, idx + 1);
        }
    }
    return false;
}

/**
 * Detecta interpolaciones dinámicas parciales dentro de class="..." (ej. sm:grid-cols-{{ ... }})
 */
function findDynamicInterpolatedClasses(content, relPath) {
    const findings = [];
    const lines = content.split(/\r?\n/);

    const classAttrRegex = /(?<![:\w-])class\s*=\s*"([^"]*)"/g;
    const dynamicConcatRegex = /([a-zA-Z0-9_:-]+-)\{\{([\s\S]*?)\}\}(-[a-zA-Z0-9_:-]+)?/g;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        if (!line.includes('class')) continue;
        let attrMatch;
        classAttrRegex.lastIndex = 0;
        while ((attrMatch = classAttrRegex.exec(line)) !== null) {
            const classVal = attrMatch[1];
            let m;
            dynamicConcatRegex.lastIndex = 0;
            while ((m = dynamicConcatRegex.exec(classVal)) !== null) {
                findings.push({
                    file: relPath,
                    line: i + 1,
                    expression: m[0].trim(),
                });
            }
        }
    }
    return findings;
}

/**
 * Limpia expresiones dinámicas de Blade/PHP dentro de atributos class="..."
 */
function stripDynamicExpressions(raw) {
    let cleaned = raw;
    // Eliminar primero cualquier concatenación parcial tipo sm:grid-cols-{{ ... }} para que no quede el prefijo roto
    cleaned = cleaned.replace(/[a-zA-Z0-9_:-]+-\{\{[\s\S]*?\}\}(-[a-zA-Z0-9_:-]+)?/g, ' ');

    // Conservar literales de clases entre comillas simples dentro de bloques {{ ... }} (ej. ternarios)
    cleaned = cleaned.replace(/\{\{([\s\S]*?)\}\}/g, (_, inner) => {
        const quoted = [];
        const quoteRegex = /'([a-zA-Z0-9_:\-\[\]\/\.#\s]+)'/g;
        let m;
        while ((m = quoteRegex.exec(inner)) !== null) {
            const val = m[1].trim();
            if (
                (val.includes('-') || val.includes(' ')) &&
                !/^[A-Z_]+$/.test(val) &&
                !inner.includes(`request('${val}')`) &&
                !inner.includes(`routeIs('${val}')`)
            ) {
                quoted.push(val);
            }
        }
        return ' ' + quoted.join(' ') + ' ';
    });
    cleaned = cleaned.replace(/\{!![\s\S]*?!!\}/g, ' ');
    cleaned = cleaned.replace(/@\w+(\s*\([^)]*\))?/g, ' ');
    return cleaned;
}

function isLikelyValidClassToken(token) {
    if (!token || token.length < 2) return false;
    if (
        token.includes('$') ||
        token.includes('->') ||
        token.includes('=>') ||
        token.includes('?') ||
        token.includes('===') ||
        token.includes('!==') ||
        token.includes('==') ||
        token.includes('!=') ||
        token.includes('&&') ||
        token.includes('||') ||
        token.includes("'") ||
        token.includes('"') ||
        token.includes('(') ||
        token.includes(')') ||
        token.includes('{') ||
        token.includes('}') ||
        token.includes(';') ||
        token.startsWith(':') ||
        token.endsWith(':') ||
        token.endsWith(',') ||
        (token.startsWith('-') && !/^-[a-z]+-/.test(token)) ||
        /^[0-9]+$/.test(token)
    ) {
        return false;
    }
    return /^[!-~]+$/.test(token);
}

function extractLocalStyleBlock(content) {
    const matches = content.match(/<style\b[^>]*>[\s\S]*?<\/style>/gi);
    return matches ? matches.join('\n') : '';
}

function extractClassesFromBlade(content, isComponentFile = false) {
    const found = new Set();

    // 1. Atributos estáticos class="..." (excluyendo :class="..." y x-bind:class="...")
    const classAttrRegex = /(?<![:\w-])class\s*=\s*"([^"]*)"/g;
    let match;
    while ((match = classAttrRegex.exec(content)) !== null) {
        const cleaned = stripDynamicExpressions(match[1]);
        for (const token of cleaned.split(/\s+/)) {
            const trimmed = token.trim();
            if (isLikelyValidClassToken(trimmed)) {
                found.add(trimmed);
            }
        }
    }

    // 2. $attributes->merge(['class' => '...'])
    const mergeRegex = /['"]class['"]\s*=>\s*(['"])([\s\S]*?)\1/g;
    while ((match = mergeRegex.exec(content)) !== null) {
        const cleaned = stripDynamicExpressions(match[2]);
        for (const token of cleaned.split(/\s+/)) {
            const trimmed = token.trim();
            if (isLikelyValidClassToken(trimmed)) {
                found.add(trimmed);
            }
        }
    }

    // 3. En componentes Blade, extraer también strings de variantes PHP (match / arrays de clases)
    if (isComponentFile) {
        const phpClassStringRegex = /=>\s*'([^'<>]+)'/g;
        while ((match = phpClassStringRegex.exec(content)) !== null) {
            if (/[A-ZÁÉÍÓÚÑ]/.test(match[1]) && !match[1].includes('[')) continue;
            for (const token of match[1].split(/\s+/)) {
                const trimmed = token.trim();
                if (isLikelyValidClassToken(trimmed) && (trimmed.includes('-') || trimmed.includes(':'))) {
                    found.add(trimmed);
                }
            }
        }
    }

    return found;
}

/**
 * Cuenta ocurrencias de alias temporales en un archivo Blade
 */
function countTemporaryAliasesInFile(content) {
    const counts = new Map();
    for (const alias of TEMPORARY_ALIASES) {
        const escaped = alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(?<![\\w-])${escaped}(?![\\w-])`, 'g');
        const matches = content.match(regex);
        if (matches && matches.length > 0) {
            counts.set(alias, matches.length);
        }
    }
    return counts;
}

function main() {
    if (!fs.existsSync(MANIFEST_PATH)) {
        console.error(`ERROR: No existe ${MANIFEST_PATH}. Ejecuta "npm run build" primero.`);
        process.exit(1);
    }

    const manifest = JSON.parse(fs.readFileSync(MANIFEST_PATH, 'utf8'));
    const cssEntry = manifest['resources/css/app.css'];
    if (!cssEntry || !cssEntry.file) {
        console.error('ERROR: No se encontró "resources/css/app.css" en public/build/manifest.json.');
        process.exit(1);
    }

    const cssPath = path.join(ROOT, 'public', 'build', cssEntry.file);
    if (!fs.existsSync(cssPath)) {
        console.error(`ERROR: No existe el archivo CSS compilado: ${cssPath}`);
        process.exit(1);
    }

    const cssContent = fs.readFileSync(cssPath, 'utf8');
    const bladeFiles = walkBladeFiles(VIEWS_DIR);

    const classUsages = new Map();
    const localStylesByFile = new Map();
    const dynamicInterpolations = [];
    // Map<alias, Map<relPath, count>>
    const aliasReport = new Map();
    for (const alias of TEMPORARY_ALIASES) {
        aliasReport.set(alias, new Map());
    }

    for (const file of bladeFiles) {
        const relPath = path.relative(ROOT, file).replace(/\\/g, '/');
        const content = fs.readFileSync(file, 'utf8');

        const localStyle = extractLocalStyleBlock(content);
        if (localStyle) {
            localStylesByFile.set(relPath, localStyle);
        }

        const isComponentFile = relPath.startsWith('resources/views/components/');
        const classes = extractClassesFromBlade(content, isComponentFile);
        for (const cls of classes) {
            if (!classUsages.has(cls)) {
                classUsages.set(cls, new Set());
            }
            classUsages.get(cls).add(relPath);
        }

        const dyn = findDynamicInterpolatedClasses(content, relPath);
        dynamicInterpolations.push(...dyn);

        const fileAliasCounts = countTemporaryAliasesInFile(content);
        for (const [alias, count] of fileAliasCounts.entries()) {
            aliasReport.get(alias).set(relPath, count);
        }
    }

    console.log(`=== Verificación Global de Estilos y Clases (npm run check:styles) ===`);
    console.log(`CSS Compilado : public/build/${cssEntry.file} (${(cssContent.length / 1024).toFixed(1)} KB)`);
    console.log(`Vistas Blade  : ${bladeFiles.length} archivos escaneados`);
    console.log(`Clases únicas : ${classUsages.size} clases extraídas y verificadas contra el CSS\n`);

    // (a) Verificación de TODAS las clases extraídas contra el CSS compilado
    const missingClasses = [];
    for (const [cls, files] of classUsages.entries()) {
        const filesArr = Array.from(files);
        const combinedLocalStyles = filesArr.map((f) => localStylesByFile.get(f) || '').join('\n');
        if (!classExistsInCss(cls, cssContent, combinedLocalStyles)) {
            missingClasses.push({ cls, files: filesArr });
        }
    }

    console.log(`--- [A] Cobertura de Clases en CSS Compilado (${classUsages.size - missingClasses.length}/${classUsages.size}) ---`);
    if (missingClasses.length === 0) {
        console.log(`✅ 100% de las clases estáticas extraídas (${classUsages.size}) existen en el CSS compilado.`);
    } else {
        console.log(`⚠️  Clases sin regla CSS en el bundle (${missingClasses.length}):`);
        missingClasses.sort((a, b) => a.cls.localeCompare(b.cls));
        for (const item of missingClasses) {
            console.log(`  - ${item.cls.padEnd(32)} -> ${item.files.join(', ')}`);
        }
    }

    // (b) Reporte de alias temporales aún en uso con conteo por archivo
    console.log(`\n--- [B] Alias Temporales en Uso (por retirar en Fase 4) ---`);
    let totalAliasOccurrences = 0;
    let totalAliasFiles = new Set();
    for (const alias of TEMPORARY_ALIASES) {
        const fileMap = aliasReport.get(alias);
        if (fileMap.size === 0) continue;
        let aliasTotal = 0;
        const details = [];
        for (const [file, count] of fileMap.entries()) {
            aliasTotal += count;
            totalAliasOccurrences += count;
            totalAliasFiles.add(file);
            details.push(`${file} (${count})`);
        }
        console.log(`  • .${alias} — ${aliasTotal} ocurrencia(s) en ${fileMap.size} archivo(s):`);
        for (const d of details) {
            console.log(`      ${d}`);
        }
    }
    if (totalAliasOccurrences === 0) {
        console.log(`✅ 0 alias temporales en uso en resources/views.`);
    } else {
        console.log(`  Resumen Alias: ${totalAliasOccurrences} ocurrencia(s) totales en ${totalAliasFiles.size} archivo(s).`);
    }

    // (c) Clases dinámicas con interpolación Blade
    console.log(`\n--- [C] Clases Dinámicas con Interpolación Blade ---`);
    if (dynamicInterpolations.length === 0) {
        console.log(`✅ 0 clases concatenadas dinámicamente con Blade.`);
    } else {
        console.log(`⚠️  Se detectaron ${dynamicInterpolations.length} clase(s) con interpolación dinámica Blade:`);
        for (const item of dynamicInterpolations) {
            console.log(`  - ${item.file}:${item.line} -> ${item.expression}`);
        }
    }
}

main();
