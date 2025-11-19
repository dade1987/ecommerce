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

## Assegnazione Ruoli

**IMPORTANTE**: Dopo aver creato l'utente, è necessario assegnargli un ruolo per permettergli l'accesso al pannello. Il sistema richiede i ruoli `super_admin` o `tripodi`.

Per creare i ruoli e assegnarli agli utenti esistenti, eseguire:

```bash
php artisan tinker
```

Poi eseguire:

```php
use Spatie\Permission\Models\Role;
use App\Models\User;

// Crea i ruoli se non esistono
$superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
$tripodi = Role::firstOrCreate(['name' => 'tripodi', 'guard_name' => 'web']);

// Assegna super_admin a un utente specifico
$user = User::where('email', 'tua-email@example.com')->first();
$user->assignRole('super_admin');
```

Oppure per assegnare il ruolo a tutti gli utenti esistenti:

```php
use Spatie\Permission\Models\Role;
use App\Models\User;

$superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

$users = User::all();
foreach ($users as $user) {
    if (!$user->hasRole('super_admin')) {
        $user->assignRole('super_admin');
    }
}
```

## Reset Password

Se hai problemi ad accedere e vuoi resettare la password di un utente:

```bash
php artisan tinker
```

Poi eseguire:

```php
use App\Models\User;

$user = User::where('email', 'tua-email@example.com')->first();
$user->password = Hash::make('nuova-password');
$user->save();
```

## Note

- Assicurarsi di avere eseguito le migrations del database prima di creare l'utente
- L'utente creato avrà accesso completo al pannello amministrativo solo se ha il ruolo `super_admin` o `tripodi`
- Per gestire i permessi degli utenti, utilizzare Filament Shield
- Se i dati di accesso non funzionano, verificare che l'utente abbia un ruolo assegnato

