<?php

namespace App\Support;

class Monedas
{
    /**
     * Lista completa de las monedas más populares y utilizadas en el mundo y Latinoamérica.
     */
    public static function todas(): array
    {
        return [
            'COP' => [
                'codigo' => 'COP',
                'nombre' => 'Peso Colombiano (COP)',
                'simbolo' => '$',
                'pais' => 'Colombia',
            ],
            'USD' => [
                'codigo' => 'USD',
                'nombre' => 'Dólar Estadounidense (USD)',
                'simbolo' => '$',
                'pais' => 'Estados Unidos / Internacional',
            ],
            'EUR' => [
                'codigo' => 'EUR',
                'nombre' => 'Euro (EUR)',
                'simbolo' => '€',
                'pais' => 'Unión Europea',
            ],
            'MXN' => [
                'codigo' => 'MXN',
                'nombre' => 'Peso Mexicano (MXN)',
                'simbolo' => '$',
                'pais' => 'México',
            ],
            'PEN' => [
                'codigo' => 'PEN',
                'nombre' => 'Sol Peruano (PEN)',
                'simbolo' => 'S/',
                'pais' => 'Perú',
            ],
            'CLP' => [
                'codigo' => 'CLP',
                'nombre' => 'Peso Chileno (CLP)',
                'simbolo' => '$',
                'pais' => 'Chile',
            ],
            'ARS' => [
                'codigo' => 'ARS',
                'nombre' => 'Peso Argentino (ARS)',
                'simbolo' => '$',
                'pais' => 'Argentina',
            ],
            'BRL' => [
                'codigo' => 'BRL',
                'nombre' => 'Real Brasileño (BRL)',
                'simbolo' => 'R$',
                'pais' => 'Brasil',
            ],
            'VES' => [
                'codigo' => 'VES',
                'nombre' => 'Bolívar Venezolano (VES)',
                'simbolo' => 'Bs.',
                'pais' => 'Venezuela',
            ],
            'BOB' => [
                'codigo' => 'BOB',
                'nombre' => 'Boliviano (BOB)',
                'simbolo' => 'Bs.',
                'pais' => 'Bolivia',
            ],
            'UYU' => [
                'codigo' => 'UYU',
                'nombre' => 'Peso Uruguayo (UYU)',
                'simbolo' => '$',
                'pais' => 'Uruguay',
            ],
            'PYG' => [
                'codigo' => 'PYG',
                'nombre' => 'Guaraní Paraguayo (PYG)',
                'simbolo' => '₲',
                'pais' => 'Paraguay',
            ],
            'CRC' => [
                'codigo' => 'CRC',
                'nombre' => 'Colón Costarricense (CRC)',
                'simbolo' => '₡',
                'pais' => 'Costa Rica',
            ],
            'DOP' => [
                'codigo' => 'DOP',
                'nombre' => 'Peso Dominicano (DOP)',
                'simbolo' => 'RD$',
                'pais' => 'República Dominicana',
            ],
            'GTQ' => [
                'codigo' => 'GTQ',
                'nombre' => 'Quetzal Guatemalteco (GTQ)',
                'simbolo' => 'Q',
                'pais' => 'Guatemala',
            ],
            'HNL' => [
                'codigo' => 'HNL',
                'nombre' => 'Lempira Hondureño (HNL)',
                'simbolo' => 'L',
                'pais' => 'Honduras',
            ],
            'NIO' => [
                'codigo' => 'NIO',
                'nombre' => 'Córdoba Nicaragüense (NIO)',
                'simbolo' => 'C$',
                'pais' => 'Nicaragua',
            ],
            'PAB' => [
                'codigo' => 'PAB',
                'nombre' => 'Balboa Panameño (PAB)',
                'simbolo' => 'B/.',
                'pais' => 'Panamá',
            ],
            'CAD' => [
                'codigo' => 'CAD',
                'nombre' => 'Dólar Canadiense (CAD)',
                'simbolo' => 'CA$',
                'pais' => 'Canadá',
            ],
            'GBP' => [
                'codigo' => 'GBP',
                'nombre' => 'Libra Esterlina (GBP)',
                'simbolo' => '£',
                'pais' => 'Reino Unido',
            ],
            'JPY' => [
                'codigo' => 'JPY',
                'nombre' => 'Yen Japonés (JPY)',
                'simbolo' => '¥',
                'pais' => 'Japón',
            ],
            'CHF' => [
                'codigo' => 'CHF',
                'nombre' => 'Franco Suizo (CHF)',
                'simbolo' => 'CHF',
                'pais' => 'Suiza',
            ],
            'CNY' => [
                'codigo' => 'CNY',
                'nombre' => 'Yuan Chino (CNY)',
                'simbolo' => '¥',
                'pais' => 'China',
            ],
        ];
    }

    /**
     * Lista de símbolos monetarios más populares y representativos.
     */
    public static function simbolos(): array
    {
        return [
            '$'   => '$ — Signo de Peso / Dólar estándar',
            '€'   => '€ — Euro',
            '£'   => '£ — Libra Esterlina',
            '¥'   => '¥ — Yen / Yuan',
            'S/'  => 'S/ — Sol Peruano',
            'Bs.' => 'Bs. — Bolívar / Boliviano',
            'R$'  => 'R$ — Real Brasileño',
            '₡'   => '₡ — Colón Costarricense',
            '₲'   => '₲ — Guaraní Paraguayo',
            'Q'   => 'Q — Quetzal Guatemalteco',
            'RD$' => 'RD$ — Peso Dominicano',
            'L'   => 'L — Lempira Hondureño',
            'C$'  => 'C$ — Córdoba Nicaragüense',
            'B/.' => 'B/. — Balboa Panameño',
            'CA$' => 'CA$ — Dólar Canadiense',
            'CHF' => 'CHF — Franco Suizo',
        ];
    }
}
