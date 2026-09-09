<?php

namespace App\Models;

use App\Enums\EstadoDian;
use App\Enums\TipoDocumentoElectronico;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoElectronico extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'documentos_electronicos';

    protected $fillable = [
        'empresa_id',
        'documento_venta_id',
        'resolucion_id',
        'tipo',
        'cufe',
        'prefijo',
        'numero',
        'consecutivo_completo',
        'estado_dian',
        'codigo_respuesta_dian',
        'mensaje_dian',
        'xml_path',
        'pdf_path',
        'qr_data',
        'metadatos_envio',
        'metadatos_respuesta',
        'fecha_emision',
        'fecha_validacion',
    ];

    protected $casts = [
        'tipo' => TipoDocumentoElectronico::class,
        'estado_dian' => EstadoDian::class,
        'numero' => 'integer',
        'metadatos_envio' => 'array',
        'metadatos_respuesta' => 'array',
        'fecha_emision' => 'datetime',
        'fecha_validacion' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documentoVenta(): BelongsTo
    {
        return $this->belongsTo(DocumentoVenta::class, 'documento_venta_id');
    }

    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(ResolucionFacturacion::class, 'resolucion_id');
    }
}
