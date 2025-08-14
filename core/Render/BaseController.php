<?php /** @noinspection PhpUnused */

namespace Core\Render;

use Core\Facades\Container;
use Core\Http\Request;
use Core\Http\Response\HtmlResponse;
use Core\Http\Response\JsonResponse;
use Core\Http\Response\ViewResponse;
use Core\Notification\ToastNotification;
use Doctrine\ORM\EntityManager;
use Exception;

abstract class BaseController
{
    protected ?EntityManager $em {
        get => Container::get('doctrine.em');
    }

    protected ?Request $request {
        get => Request::getInstance();
    }

    protected Renderer $renderer;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (is_null($this->em)) {
            throw new Exception('EntityManager is not initialized');
        }

        $this->renderer = new Renderer();
    }

    /**
     * Vytvoří Response s view
     */
    protected function view(string $view, array $data = [], int $statusCode = 200): ViewResponse
    {
        return $this->renderer->view($view, $data, $statusCode);
    }

    /**
     * Přidá toast notifikaci
     */
    protected function toast(string $type, string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::add($type, $message, $duration, $options);
    }

    /**
     * Přidá success toast notifikaci
     */
    protected function toastSuccess(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::success($message, $duration, $options);
    }

    /**
     * Přidá error toast notifikaci
     */
    protected function toastError(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::error($message, $duration, $options);
    }

    /**
     * Přidá warning toast notifikaci
     */
    protected function toastWarning(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::warning($message, $duration, $options);
    }

    /**
     * Přidá info toast notifikaci
     */
    protected function toastInfo(string $message, int $duration = 5000, array $options = []): void
    {
        ToastNotification::info($message, $duration, $options);
    }

    /**
     * Vytvoří Response s HTML obsahem
     */
    protected function html(string $html, int $statusCode = 200): HtmlResponse
    {
        return $this->renderer->html($html, $statusCode);
    }

    /**
     * Vytvoří Response s JSON obsahem
     */
    protected function json(mixed $data, int $statusCode = 200): JsonResponse
    {
        return $this->renderer->json($data, $statusCode);
    }

    /**
     * Vytvoří JSON error Response
     */
    protected function jsonError(string $message, int $statusCode = 400): JsonResponse
    {
        return $this->renderer->jsonError($message, $statusCode);
    }

    /**
     * Vytvoří JSON success Response
     */
    protected function jsonSuccess(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return $this->renderer->jsonSuccess($data, $message, $statusCode);
    }

    /**
     * Vytvoří Response s chybou
     */
    protected function error(int $statusCode, ?string $message = null): ViewResponse
    {
        $errorData = [
            'code' => $statusCode,
            'title' => $this->getErrorTitle($statusCode),
            'message' => $message ?? $this->getErrorMessage($statusCode)
        ];

        return $this->view('errors.generic', $errorData, $statusCode);
    }

    /**
     * Vytvoří Response s 404 chybou
     */
    protected function notFound(?string $message = null): ViewResponse
    {
        return $this->error(404, $message);
    }

    /**
     * Vytvoří Response s 403 chybou
     */
    protected function forbidden(?string $message = null): ViewResponse
    {
        return $this->error(403, $message);
    }

    /**
     * Vytvoří Response s 500 chybou
     */
    protected function serverError(?string $message = null): ViewResponse
    {
        return $this->error(500, $message);
    }

    /**
     * Vytvoří redirect Response
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        $response = $this->renderer->redirect($url, $statusCode);
        $response->send();
    }

    /**
     * Získá query parametr
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->request->query($key, $default);
    }

    /**
     * Získá POST parametr
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Získá cookie
     */
    protected function cookie(string $key, mixed $default = null): mixed
    {
        return $this->request->cookie($key, $default);
    }

    /**
     * Získá soubor
     */
    protected function file(string $key): ?array
    {
        return $this->request->file($key);
    }

    /**
     * Získá všechny parametry
     */
    protected function all(): array
    {
        return $this->request->all();
    }

    /**
     * Zkontroluje, zda parametr existuje
     */
    protected function has(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * Práce se session
     */
    protected function session(?string $key = null, mixed $value = null)
    {
        $session = $this->request->getSession();

        if ($key === null) {
            return $session;
        }

        if ($value === null) {
            return $session->get($key);
        }

        $session->set($key, $value);
        return $this;
    }

    /**
     * Zkontroluje, zda je požadavek AJAX
     */
    protected function isAjax(): bool
    {
        return $this->request->isAjax();
    }

    /**
     * Vygeneruje URL pro pojmenovanou route
     * @throws Exception
     */
    protected function to(string $name, array $parameters = []): string
    {
        $router = Container::get('router');
        return $router->route($name, $parameters);
    }

    /**
     * Zkontroluje, zda je požadavek POST
     */
    protected function isPost(): bool
    {
        return $this->request->isPost();
    }

    /**
     * Zkontroluje, zda je požadavek GET
     */
    protected function isGet(): bool
    {
        return $this->request->isGet();
    }

    /**
     * Zkontroluje, zda je požadavek PUT
     */
    protected function isPut(): bool
    {
        return $this->request->isPut();
    }

    /**
     * Zkontroluje, zda je požadavek DELETE
     */
    protected function isDelete(): bool
    {
        return $this->request->isDelete();
    }

    /**
     * Zkontroluje, zda je požadavek PATCH
     */
    protected function isPatch(): bool
    {
        return $this->request->isPatch();
    }

    /**
     * Zkontroluje, zda view existuje
     */
    protected function viewExists(string $view): bool
    {
        return $this->renderer->viewExists($view);
    }

    /**
     * Sdílí data se všemi views
     */
    protected function share(array|string $key, mixed $value = null): self
    {
        $this->renderer->share($key, $value);
        return $this;
    }

    /**
     * Získá IP adresu klienta
     */
    protected function getClientIp(): ?string
    {
        return $this->request->getClientIp();
    }

    /**
     * Získá User Agent
     */
    protected function getUserAgent(): ?string
    {
        return $this->request->getUserAgent();
    }

    /**
     * Získá referer
     */
    protected function getReferer(): ?string
    {
        return $this->request->getReferer();
    }

    /**
     * Získá HTTP header
     */
    protected function header(string $key, ?string $default = null): ?string
    {
        return $this->request->header($key, $default);
    }

    /**
     * Získá všechny hlavičky
     */
    protected function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * Zkontroluje, zda hlavička existuje
     */
    protected function hasHeader(string $key): bool
    {
        return $this->request->hasHeader($key);
    }

    /**
     * Získá content type
     */
    protected function getContentType(): ?string
    {
        return $this->request->getContentType();
    }

    /**
     * Získá raw body
     */
    protected function getBody(): string
    {
        return $this->request->getBody();
    }

    /**
     * Získá JSON body
     */
    protected function getJson(): ?array
    {
        return $this->request->getJson();
    }

    /**
     * Zkontroluje, zda je požadavek JSON
     */
    protected function isJson(): bool
    {
        return $this->request->isJson();
    }

    /**
     * Zkontroluje, zda je požadavek multipart/form-data
     */
    protected function isMultipart(): bool
    {
        return $this->request->isMultipart();
    }

    /**
     * Zkontroluje, zda je požadavek application/x-www-form-urlencoded
     */
    protected function isForm(): bool
    {
        return $this->request->isForm();
    }

    /**
     * Získá všechny soubory
     */
    protected function getFiles(): array
    {
        return $this->request->getFiles();
    }

    /**
     * Zkontroluje, zda má požadavek soubory
     */
    protected function hasFiles(): bool
    {
        return $this->request->hasFiles();
    }

    /**
     * Získá všechny cookies
     */
    protected function getCookies(): array
    {
        return $this->request->getCookies();
    }

    /**
     * Zkontroluje, zda cookie existuje
     */
    protected function hasCookie(string $key): bool
    {
        return $this->request->hasCookie($key);
    }

    /**
     * Získá všechny server proměnné
     */
    protected function getServer(): array
    {
        return $this->request->getServer();
    }

    /**
     * Získá server proměnnou
     */
    protected function server(string $key, mixed $default = null): mixed
    {
        return $this->request->server($key, $default);
    }

    /**
     * Zkontroluje, zda server proměnná existuje
     */
    protected function hasServer(string $key): bool
    {
        return $this->request->hasServer($key);
    }

    /**
     * Získá všechny externí session data
     */
    protected function getExternalSession(bool $asArray = false): array|object
    {
        return $this->request->getExternalSession($asArray);
    }

    /**
     * Zkontroluje, zda aktuální URI odpovídá danému patternu
     */
    protected function is(string $pattern): bool
    {
        return $this->request->is($pattern);
    }

    /**
     * Zkontroluje, zda URI začíná daným prefixem
     */
    protected function startsWith(string $prefix): bool
    {
        return $this->request->startsWith($prefix);
    }

    /**
     * Zkontroluje, zda URI končí daným suffixem
     */
    protected function endsWith(string $suffix): bool
    {
        return $this->request->endsWith($suffix);
    }

    /**
     * Získá nadpis pro daný status kód
     */
    private function getErrorTitle(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Špatný požadavek',
            401 => 'Neautorizovaný přístup',
            403 => 'Přístup zamítnut',
            404 => 'Stránka nenalezena',
            405 => 'Metoda není povolena',
            408 => 'Časový limit vypršel',
            429 => 'Příliš mnoho požadavků',
            500 => 'Interní chyba serveru',
            502 => 'Špatná brána',
            503 => 'Služba není dostupná',
            504 => 'Časový limit brány',
            default => 'Chyba'
        };
    }

    /**
     * Získá výchozí zprávu pro daný status kód
     */
    private function getErrorMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Požadavek obsahuje neplatné údaje.',
            401 => 'Pro přístup k této stránce se musíte přihlásit.',
            403 => 'Nemáte oprávnění k přístupu k této stránce.',
            404 => 'Požadovaná stránka nebyla nalezena.',
            405 => 'Použitá metoda není pro tuto stránku povolena.',
            408 => 'Časový limit požadavku vypršel.',
            429 => 'Příliš mnoho požadavků. Zkuste to prosím později.',
            500 => 'Došlo k neočekávané chybě na serveru.',
            502 => 'Server dostal neplatnou odpověď od upstream serveru.',
            503 => 'Služba je dočasně nedostupná.',
            504 => 'Časový limit brány vypršel.',
            default => 'Došlo k neočekávané chybě.'
        };
    }
}
