# Setup Filament Admin Panel

## Creazione Utente Amministratore

Per creare un nuovo utente amministratore per il pannello Filament, eseguire il seguente comando:

```bash
php artisan filament:user
```

Il comando richiederà:
- Nome
- Email
- Password
- Conferma password

Dopo aver inserito i dati, l'utente sarà creato e potrà accedere al pannello amministrativo Filament.

## Note

- Assicurarsi di avere eseguito le migrations del database prima di creare l'utente
- L'utente creato avrà accesso completo al pannello amministrativo
- Per gestire i permessi degli utenti, utilizzare Filament Shield

