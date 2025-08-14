# Nová architektura s oddělenými odpovědnostmi

## Přehled

Aplikace byla refaktorována podle principu Single Responsibility Principle (SRP) s jasným oddělením zodpovědností mezi komponenty.

## Komponenty

### 1. Response třídy (`core/Http/Response/`)

**Účel**: Nesené tělo, kód, hlavičky; má `send()` metodu

- **`AbstractResponse`** - Základní abstraktní třída pro všechny Response objekty
- **`HtmlResponse`** - Pro HTML odpovědi
- **`JsonResponse`** - Pro JSON odpovědi s metodami `error()` a `success()`
- **`RedirectResponse`** - Pro přesměrování
- **`ViewResponse`** - Pro view odpovědi s logováním a událostmi

### 2. Request (`core/Http/Request.php`)

**Účel**: Vstupy, metoda, AJAX, atd.

- Zpracování všech HTTP požadavků
- Metody pro získání dat (query, input, cookie, file)
- Kontrola typu požadavku (isAjax, isPost, isGet, atd.)
- Práce s hlavičkami, cookies, soubory
- Session management

### 3. Renderer (`core/Render/Renderer.php`)

**Účel**: Render a tvorba Response (bez side-effectů)

- Pouze vytváří Response objekty
- Žádné přímé HTTP operace
- Metody: `view()`, `html()`, `json()`, `redirect()`
- Sdílení dat s views

### 4. Middleware (`core/Routing/Middleware.php`)

**Účel**: auth/guest, session, CSRF, atd.

- Autentifikace a autorizace
- Session management
- CSRF ochrana
- Další bezpečnostní middleware

### 5. Controller (`core/Render/BaseController.php`)

**Účel**: Skládá Request + doménu + Renderer → Response

- Kombinuje Request, doménovou logiku a Renderer
- Vrací Response objekty
- Poskytuje helper metody pro běžné operace
- Správa chybových stavů

### 6. Logger (`core/Logging/RenderLogger.php`)

**Účel**: Metriky renderu (čas, velikost)

- Logování času renderu
- Sledování velikosti obsahu
- Metriky paměti
- Debug informace

### 7. EventBus (`core/Events/EventBus.php`)

**Účel**: Události a metriky

- Události před/po renderu
- Události před/po odeslání response
- Možnost registrace listenerů
- Sledování událostí

### 8. Router (`core/Routing/Router.php`)

**Účel**: Směrování požadavků

- Definice routes
- Zpracování požadavků
- Aplikace middleware
- Logování zpracování

## Výhody nové architektury

### 1. Čitelnost
- Každá třída má jasně definovanou zodpovědnost
- Kód je snadněji pochopitelný a udržovatelný

### 2. Testovatelnost
- Komponenty lze testovat izolovaně
- Snadné mockování závislostí
- Jasné rozhraní mezi komponenty

### 3. Bez "úniků" odpovědnosti
- Žádná třída neobsahuje nesouvisející funkcionalitu
- Jasné hranice mezi komponenty

### 4. Flexibilita
- Snadné přidávání nových Response typů
- Možnost rozšíření middleware
- Event-driven architektura

### 5. Výkon
- Logování pouze při potřebě
- Optimalizované zpracování požadavků
- Metriky pro monitoring

## Příklad použití

```php
class ExampleController extends BaseController
{
    public function index(): ViewResponse
    {
        // Request data
        $search = $this->input('search');

        // Doménová logika
        $data = $this->em->getRepository(Example::class)->findBySearch($search);

        // Response přes Renderer
        return $this->view('example.index', ['data' => $data]);
    }

    public function api(): JsonResponse
    {
        $data = $this->getData();
        return $this->jsonSuccess($data);
    }

    public function redirect(): void
    {
        $this->redirect('/dashboard');
    }
}
```

## Migrace z starého systému

1. **Response objekty**: Používejte specializované Response třídy místo obecné `Response`
2. **Renderer**: Používejte `$this->renderer` v controllerech
3. **Logging**: Automatické logování přes `RenderLogger`
4. **Events**: Registrujte listenery v `EventBus` pro custom logiku

## Monitoring a debug

- **Metriky**: `RenderLogger::getMetrics()`
- **Události**: `EventBus::getEvents()`
- **Průměrný čas**: `RenderLogger::getAverageRenderTime()`
- **Celková velikost**: `RenderLogger::getTotalContentSize()`
