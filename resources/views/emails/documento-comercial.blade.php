<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $asunto }}</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;">
        <tr>
            <td style="padding:20px 24px;border-bottom:1px solid #e2e8f0;font-size:16px;font-weight:bold;color:#0f172a;">
                {{ $empresaNombre }}
            </td>
        </tr>
        <tr>
            <td style="padding:24px;font-size:14px;line-height:1.6;">
                {!! nl2br(e($mensaje)) !!}
                <p style="margin-top:20px;font-size:12px;color:#64748b;">El documento va adjunto a este correo en formato PDF.</p>
            </td>
        </tr>
    </table>
</body>
</html>
