<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trascrizione chat</title>
</head>
<body style="margin:0; padding:0; background:#0f172a; color:#e5e7eb; font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <div style="max-width:680px; margin:0 auto; padding:16px;">
    <div style="background:#111827; border:1px solid #334155; border-radius:12px; overflow:hidden;">
      <div style="padding:14px 16px; border-bottom:1px solid #334155; background:#0b1220;">
        <h2 style="margin:0; font-size:18px; color:#ffffff;">Trascrizione chat - Thread {{ e($threadId) }}</h2>
      </div>
      <div style="padding:16px;">
        @foreach ($messages as $m)
          @php
            $role = $m->role === 'user' ? 'Utente' : 'Assistente';
            $raw = $m->content ?? '';
            // Escape base
            $safe = e($raw);
            // Linkify semplice
            $safe = preg_replace('~(https?://\S+)~', '<a href="$1" style="color:#93c5fd" target="_blank" rel="noopener noreferrer">$1</a>', $safe);
            // Preserva nuove righe
            $safe = nl2br($safe);
          @endphp
          <div style="margin-bottom:12px; padding:12px; border:1px solid #334155; border-radius:10px; background:#0b1220;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
              <span style="display:inline-block; padding:2px 8px; border-radius:9999px; background:#4f46e5; color:#ffffff; font-size:12px;">{{ $role }}</span>
              <span style="color:#94a3b8; font-size:12px;">{{ optional($m->created_at)->format('Y-m-d H:i') }}</span>
            </div>
            <div style="white-space:pre-wrap; line-height:1.5; font-size:14px; color:#e5e7eb;">{!! $safe !!}</div>
          </div>
        @endforeach
      </div>
      <div style="padding:12px 16px; border-top:1px solid #334155; background:#0b1220; color:#94a3b8; font-size:12px;">
        Invio automatico trascrizione. Non rispondere a questa email.
      </div>
    </div>
  </div>
</body>
</html>


