# Arcadia CRM

Systém pro správu vztahů se zákazníky s podporou Blade šablon a Tailwind CSS.

## Instalace

### 1. PHP závislosti
```bash
composer install
```

### 2. Node.js závislosti
```bash
npm install
```

### 3. Databáze
```bash
# Spuštění migrací pro vytvoření tabulek
php database/migrate.php

# Načtení testovacích dat
php database/seed.php
```

### 4. Sestavení CSS
```bash
# Pro vývoj (s watch mode)
npm run dev

# Pro produkci
npm run build
```

## Struktura projektu

```
├── app/
│   ├── Controllers/          # Controllers
│   └── Entities/             # Doctrine ORM entity
├── core/
│   └── View.php             # Blade template engine
├── database/
│   ├── migrations/           # SQL migrace
│   ├── seeds/               # Testovací data
│   ├── migrate.php          # Skript pro migrace
│   └── seed.php             # Skript pro seed data
├── resources/
│   ├── views/               # Blade šablony
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   └── home.blade.php
│   └── css/
│       └── app.css          # Hlavní CSS soubor
├── public/
│   └── css/
│       └── app.css          # Kompilovaný CSS
├── var/
│   └── cache/
│       └── views/           # Cache pro kompilované šablony
└── config/
```

## Databázové tabulky

### Users
- Správa uživatelů systému
- Role: admin, manager, user

### Customers
- Zákazníci a klienti
- Kategorie: person, company
- Status: active, inactive, prospect

### Contacts
- Kontakty s zákazníky
- Typy: email, phone, meeting, note
- Status: scheduled, completed, cancelled

### Deals
- Obchodní příležitosti
- Fáze: prospecting, qualification, proposal, negotiation, closed_won, closed_lost
- Status: active, won, lost, cancelled

## Použití Blade šablon

### V controlleru:

```php
<?php

namespace App\Controllers;

use Core\Render\View;

class HomeController
{
    public function index()
    {
        $data = [
            'title' => 'Domů',
            'users' => $users
        ];

        return View::render('home', $data);
    }
}
```

### V šabloně:
```blade
@extends('layouts.app')

@section('title', 'Domů')

@section('content')
    <div class="card">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>

        @foreach($users as $user)
            <div class="p-4">{{ $user->name }}</div>
        @endforeach
    </div>
@endsection
```

## Moderní Sidebar

Aplikace obsahuje moderní sidebar s:
- Responzivním designem pro mobily
- Animovaným otevíráním/zavíráním
- Overlay pro mobilní zařízení
- Automatické zavírání na velkých obrazovkách

### Navigační položky:
- Dashboard - Přehled
- Zákazníci - Správa zákazníků
- Kontakty - Historie kontaktů
- Obchody - Obchodní příležitosti
- Reporty - Analytika
- Nastavení - Konfigurace

## Tailwind CSS

Projekt používá Tailwind CSS pro styling. Vlastní styly můžete přidat do `resources/css/app.css`.

### Dostupné utility třídy:
- `btn-primary` - Primární tlačítko
- `btn-secondary` - Sekundární tlačítko
- `card` - Karta s stínem
- `form-input` - Input pole
- `form-label` - Label pro formuláře

## Vývoj

### Automatický refresh a hot reload

Pro spuštění s automatickým refresh při změnách:

```bash
# Spustí vše najednou (CSS watch, PHP server, WebSocket)
npm start

# Nebo jednotlivě:
npm run dev          # CSS watch mode
php -S localhost:8000 -t public  # PHP server
php tools/websocket-server.php   # WebSocket server
```

### Tracy Debugger

Tracy debugger je automaticky aktivní ve vývojovém režimu s custom panely:

- **💾 DB** - Databázové dotazy a statistiky
- **📄 Views** - Rendered šablony a časy
- **⚡ Cache** - Cache hit/miss statistiky
- **🚀 Perf** - Výkonnostní metriky

### Vytváření šablon

1. Vytvářejte nové šablony v `resources/views/`

2. Používejte Blade direktivy:
   - `@extends()` - Rozšíření layoutu
   - `@section()` - Definice sekce
   - `@yield()` - Výstup sekce
   - `@push()` / `@stack()` - Stack pro skripty
   - `@if`, `@foreach`, `@while` - Kontrolní struktury
   - `{{ }}` - Výstup proměnných
   - `{!! !!}` - Výstup HTML

## Produkční nasazení

1. Sestavte CSS:
   ```bash
   npm run build
   ```

2. Spusťte migrace:
   ```bash
   php database/migrate.php
   ```

3. Zajistěte, aby adresář `var/cache/views` byl zapisovatelný

4. Nastavte správné oprávnění pro `public/css/app.css`
