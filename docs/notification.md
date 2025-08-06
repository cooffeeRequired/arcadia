# Notification Systém

Moderní notification systém s podporou Toast a Flash notifikací.

## Instalace

Notification systém je již integrován do frameworku. Stačí použít třídu `Core\Notification\Notification`.

## Základní použití

### Inicializace

```php
use Core\Notification\Notification;

// Inicializace systému
Notification::init();
```

### Základní notifikace

```php
// Úspěch
Notification::success('Operace byla úspěšná!');

// Chyba
Notification::error('Nastala chyba!');

// Varování
Notification::warning('Pozor na toto!');

// Informace
Notification::info('Informace pro vás');

// Rychlé metody
Notification::ok('OK zpráva');
Notification::fail('Fail zpráva');
Notification::warn('Warn zpráva');
```

### Toast notifikace

```php
// Základní toast
Notification::toast('Toast zpráva');

// Typované toast notifikace
Notification::toastSuccess('Úspěch!');
Notification::toastError('Chyba!');
Notification::toastWarning('Varování!');
Notification::toastInfo('Informace!');

// Rychlé metody
Notification::okToast('OK toast');
Notification::failToast('Fail toast');
Notification::warnToast('Warn toast');

// S vlastními možnostmi
Notification::toastSuccess('Zpráva', 3000, ['position' => 'top-right']);
```

### Flash notifikace

```php
// Základní flash
Notification::flash('Flash zpráva');

// Typované flash notifikace
Notification::flashSuccess('Úspěch!');
Notification::flashError('Chyba!');
Notification::flashWarning('Varování!');
Notification::flashInfo('Informace!');

// Rychlé metody
Notification::okFlash('OK flash');
Notification::failFlash('Fail flash');
Notification::warnFlash('Warn flash');

// S vlastními možnostmi
Notification::flashSuccess('Zpráva', ['position' => 'bottom-center', 'autoHide' => false]);
```

### Pokročilé použití

```php
// Kombinované notifikace s možnostmi
Notification::success('Zpráva', [
    'type' => 'toast', // 'toast', 'flash', 'both'
    'duration' => 3000,
    'toast' => ['position' => 'top-right'],
    'flash' => ['position' => 'bottom-center', 'autoHide' => false]
]);

// Persistent flash notifikace (nezavře se automaticky)
Notification::persistent('success', 'Důležitá zpráva');

// Non-dismissible flash notifikace (nejde zavřít)
Notification::nonDismissible('warning', 'Systémová zpráva');
```

## Renderování

### V šabloně (Blade)

```php
// Render všech notifikací a skriptů
echo Notification::render();

// Samostatné renderování
echo Notification::renderAll();        // HTML notifikace
echo Notification::renderAllScripts(); // JavaScript skripty

// Toast a Flash samostatně
echo Notification::renderToasts();     // Toast HTML
echo Notification::renderFlash();      // Flash HTML
```

### V layout souboru

```html
<!DOCTYPE html>
<html>
<head>
    <title>Moje aplikace</title>
</head>
<body>
    <!-- Obsah stránky -->
    
    <!-- Notification systém -->
    <?php echo Notification::render(); ?>
</body>
</html>
```

## Konfigurace

### Globální nastavení

```php
// Nastavení celého systému
Notification::configure([
    'toast' => [
        'position' => 'top-right',
        'outlineClass' => 'custom-outline',
        'contentClass' => 'custom-content'
    ],
    'flash' => [
        'position' => 'bottom-center',
        'containerClass' => 'custom-container',
        'dismissible' => true,
        'autoHide' => true,
        'duration' => 5000
    ]
]);

// Samostatné nastavení
Notification::configureToast(['position' => 'top-left']);
Notification::configureFlash(['position' => 'bottom-right']);
```

### Pozice notifikací

**Toast pozice:**
- `top-right` (výchozí)
- `top-left`
- `bottom-left`
- `bottom-right`
- `center`

**Flash pozice:**
- `top-center` (výchozí)
- `top-right`
- `top-left`
- `bottom-right`
- `bottom-left`
- `bottom-center`

## API Reference

### Základní metody

| Metoda | Popis |
|--------|-------|
| `init()` | Inicializuje notification systém |
| `success($message, $options)` | Přidá success notifikaci |
| `error($message, $options)` | Přidá error notifikaci |
| `warning($message, $options)` | Přidá warning notifikaci |
| `info($message, $options)` | Přidá info notifikaci |
| `ok($message, $options)` | Rychlá metoda pro success |
| `fail($message, $options)` | Rychlá metoda pro error |
| `warn($message, $options)` | Rychlá metoda pro warning |

### Toast metody

| Metoda | Popis |
|--------|-------|
| `toast($message, $duration, $options)` | Základní toast |
| `toastSuccess($message, $duration, $options)` | Success toast |
| `toastError($message, $duration, $options)` | Error toast |
| `toastWarning($message, $duration, $options)` | Warning toast |
| `toastInfo($message, $duration, $options)` | Info toast |
| `okToast($message, $duration, $options)` | Rychlá success toast |
| `failToast($message, $duration, $options)` | Rychlá error toast |
| `warnToast($message, $duration, $options)` | Rychlá warning toast |

### Flash metody

| Metoda | Popis |
|--------|-------|
| `flash($message, $options)` | Základní flash |
| `flashSuccess($message, $options)` | Success flash |
| `flashError($message, $options)` | Error flash |
| `flashWarning($message, $options)` | Warning flash |
| `flashInfo($message, $options)` | Info flash |
| `okFlash($message, $options)` | Rychlá success flash |
| `failFlash($message, $options)` | Rychlá error flash |
| `warnFlash($message, $options)` | Rychlá warning flash |
| `persistent($type, $message, $options)` | Persistent flash |
| `nonDismissible($type, $message, $options)` | Non-dismissible flash |

### Render metody

| Metoda | Popis |
|--------|-------|
| `render()` | Render všech notifikací a skriptů |
| `renderAll()` | Render HTML notifikací |
| `renderAllScripts()` | Render JavaScript skriptů |
| `renderToasts()` | Render toast HTML |
| `renderFlash()` | Render flash HTML |

### Konfigurace metody

| Metoda | Popis |
|--------|-------|
| `configure($settings)` | Globální konfigurace |
| `configureToast($settings)` | Konfigurace toast |
| `configureFlash($settings)` | Konfigurace flash |
| `getConfig()` | Získá aktuální konfiguraci |

### Utility metody

| Metoda | Popis |
|--------|-------|
| `hasAny()` | Zkontroluje, zda existují notifikace |
| `clear()` | Vyčistí všechny notifikace |
| `getToasts()` | Získá všechny toast notifikace |
| `getFlash()` | Získá všechny flash notifikace |

## Příklady použití

### V Controller

```php
class UserController
{
    public function store()
    {
        try {
            // Vytvoření uživatele
            $user = User::create($request->validated());
            
            Notification::success('Uživatel byl úspěšně vytvořen!');
            
            return redirect()->route('users.index');
        } catch (Exception $e) {
            Notification::error('Nepodařilo se vytvořit uživatele: ' . $e->getMessage());
            
            return back()->withInput();
        }
    }
}
```

### V Middleware

```php
class AuthMiddleware
{
    public function handle($request, $next)
    {
        if (!auth()->check()) {
            Notification::warning('Pro přístup k této stránce se musíte přihlásit.');
            return redirect()->route('login');
        }
        
        return $next($request);
    }
}
```

### V Event Listener

```php
class UserRegisteredListener
{
    public function handle(UserRegistered $event)
    {
        Notification::toastSuccess('Vítejte v aplikaci!', 10000);
        Notification::flashInfo('Zkontrolujte svůj email pro potvrzení účtu.');
    }
}
```

## Stylování

Notification systém používá Tailwind CSS třídy. Můžete upravit vzhled pomocí CSS:

```css
/* Vlastní styly pro toast */
#toast-container {
    z-index: 9999;
}

/* Vlastní styly pro flash */
#flash-container {
    z-index: 10000;
}

/* Vlastní animace */
.toast-enter {
    animation: slideIn 0.3s ease-out;
}

.flash-enter {
    animation: fadeIn 0.3s ease-out;
}
```

## Poznámky

- Notification systém automaticky inicializuje session pro správu notifikací
- Toast notifikace se automaticky zavřou po nastavené době (výchozí 5 sekund)
- Flash notifikace lze zavřít kliknutím na X tlačítko
- Všechny notifikace jsou escapované pro bezpečnost
- Systém podporuje Unicode znaky a české texty 