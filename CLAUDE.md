# CLAUDE.md — NovaPay API Package

> Контекст для AI асистентів, що працюють з цим пакетом.

---

## Що це

Laravel пакет `sashalenz/novapay-api` — SOAP клієнт для **NovaPay Business Cabinet API v2.0**.
Документація API: «API інструкція (березень 2026)» (42 стор., WSDL-сервіс).

SOAP endpoint: `https://business.novapay.ua/Services/ClientAPIService.svc`
SOAPAction prefix: `http://tempuri.org/IClientAPIService/{MethodName}`
Namespace: `http://tempuri.org/`

---

## Архітектура

```
src/
├── NovapayApi.php              — фасад з 5 статичними методами
├── NovapayApiServiceProvider   — реєструє конфіг
├── SoapRequest.php             — SOAP XML builder + HTTP + XML parser
├── ApiModels/
│   ├── BaseModel.php           — proxy, call(), generateRequestRef()
│   ├── Auth.php                — jwt(), jwtFromConfig(), preAuthenticate(), authenticate(), refresh()
│   ├── Clients.php             — list()
│   ├── Accounts.php            — list(), balance(), payments(), extract(), turns()
│   ├── Payments.php            — create(), recallableList(), recall()
│   └── Registers.php           — get(), download()
├── Enums/
│   ├── AccountStatus.php       — string backed: Active|Closed|OnApproval|Arrested
│   ├── DateType.php            — int backed: 0-3
│   └── RegisterFileExtension.php — string backed: XLS|CSV
├── Exceptions/
│   └── NovapayApiException.php
├── RequestData/                — spatie/laravel-data DTOs (вхідні параметри)
├── ResponseData/               — spatie/laravel-data DTOs (відповіді)
└── Types/                      — вкладені типи: Client, Account, AccountTurn, RecallPayment, ApiError
```

---

## Ключові патерни

### SOAP замість REST
На відміну від `monobank-api` / `privat24-business-api` (JSON REST), NovaPay використовує SOAP/XML.
`SoapRequest` будує XML envelope, відправляє POST з `Content-Type: text/xml`, парсить відповідь через `simplexml_load_string(..., LIBXML_NOCDATA)`.

### Fluent API для авторизації
```php
NovapayApi::accounts()->withJwt($jwt)->balance(49);
NovapayApi::accounts()->withPrincipal($principal)->balance(49);
```
`withJwt()` / `withPrincipal()` — взаємовиключні. JWT рекомендований.

### Відповіді — завжди Data objects
Всі методи повертають `ResponseData\*` клас зі `spatie/laravel-data`.
Перевірка успішності: `$response->isSuccessful()` (перевіряє `result === 'ok'`).
Логічна помилка API: `$response->error` типу `?ApiError` з полями `status` і `title`.

### XML в CDATA
`GetPaymentsList` і `GetAccountExtract` повертають вкладений XML як CDATA у полі `payments`/`extract`.
Парс через `$response->parsePayments()` / `$response->parseExtract()` — повертає `array`.

### normalizeCollection()
Метод у кожному ApiModel нормалізує XML колекції — один елемент приходить як `['key' => [...]]`, кілька — як `['key' => [[...], [...]]]`. Завжди повертає `array` для `DataCollection`.

---

## Методи API (всі 15)

| # | Метод | ApiModel | PHP метод |
|---|---|---|---|
| 3.1 | `UserAuthenticationJWT` | `Auth` | `jwt()` / `jwtFromConfig()` |
| 3.2 | `PreUserAuthentication` | `Auth` | `preAuthenticate()` |
| 3.3 | `UserAuthentication` | `Auth` | `authenticate()` |
| 3.4 | `RefreshUserAuthentication` | `Auth` | `refresh()` |
| 3.5 | `GetClientsList` | `Clients` | `list()` |
| 3.6 | `GetAccountsList` | `Accounts` | `list(clientId)` |
| 3.7 | `GetAccountRest` | `Accounts` | `balance(accountId)` |
| 3.8 | `GetPaymentsList` | `Accounts` | `payments(...)` |
| 3.9 | `GetAccountExtract` | `Accounts` | `extract(...)` |
| 3.10 | `CreatePayment` | `Payments` | `create(array $header)` |
| 3.11 | `GetRecallPaymentsList` | `Payments` | `recallableList()` |
| 3.12 | `RecallPayment` | `Payments` | `recall(paymentId)` |
| 3.13 | `GetAccountTurns` | `Accounts` | `turns(...)` |
| 3.14 | `GetRegister` | `Registers` | `get(clientId, from, into, fileExtension)` |
| 3.15 | `DownloadRegister` | `Registers` | `download(id)` |

---

## Типи ResponseData

```
Auth/JwtAuthResponse          → jwt, expiration, refresh_token, public_certificate
Auth/PreAuthResponse          → user_id(?string), code_operation_otp(?string), temp_principal
                                getUserId():?int, getCodeOperationOtp():?int
Auth/UserAuthResponse         → principal, expiration
Auth/RefreshAuthResponse      → new_principal, expiration

Clients/GetClientsListResponse          → clients: DataCollection<Client>
Accounts/GetAccountsListResponse        → accounts: DataCollection<Account>
Accounts/GetAccountRestResponse         → confirmed_balance, available_balance, projected_balance
Accounts/GetPaymentsListResponse        → payments(?string CDATA), parsePayments(): array
Accounts/GetAccountExtractResponse      → extract(?string CDATA), parseExtract(): array{head,payments}
Accounts/GetAccountTurnsResponse        → turns: DataCollection<AccountTurn>
Payments/CreatePaymentResponse          → result
Payments/GetRecallPaymentsListResponse  → payments: DataCollection<RecallPayment>
Payments/RecallPaymentResponse          → result
Registers/GetRegisterResponse           → statement_id(int), created_datetime
Registers/DownloadRegisterResponse      → id, status, url, file_type, file_name
```

---

## Відомі нюанси

### JWT ротація токенів
Кожен виклик `UserAuthenticationJWT` анулює старий `refresh_token`.
Новий `refresh_token` і `public_certificate` треба зберегти одразу після відповіді.
У проекті A20 це робиться через налаштування в БД або `config/cache`.

### PreAuthResponse — user_id як рядок
`PreAuthResponse::$user_id` і `$code_operation_otp` зберігаються як `?string` (XML дає рядки).
Використовуй геттери: `getUserId(): ?int`, `getCodeOperationOtp(): ?int`.

### AccountStatus enum
`Account::$statuscode` може бути `null` якщо поле відсутнє у відповіді.
Значення: `Active`(1), `Closed`(0), `OnApproval`(4), `Arrested`(2).

### Числові поля в типах
`AccountTurn` і `RecallPayment` мають `?float` / `?string` поля — XML може не включати всі поля.

### Typed class constants (PHP 8.3+)
У `SoapRequest.php` константи навмисно **без** типу (`const SOAP_NS = ...`),
бо пакет підтримує PHP 8.2+, а typed constants — тільки PHP 8.3+.

---

## Тести

```bash
vendor/bin/pest          # всі тести
vendor/bin/pest --filter Auth   # тільки Auth тести
vendor/bin/pest --coverage
```

Тести використовують `Http::fake()` з реальними SOAP XML прикладами з документації.
`TestCase::soapResponse(method, fields)` — хелпер для побудови SOAP envelope.
Якщо `$value` починається з `<` — вбудовується як raw XML (для вкладених колекцій).

---

## Додавання нового методу

1. Додай PHP метод у відповідний `ApiModels/*.php`
2. Створи `RequestData/Group/MethodRequest.php` (якщо є складні параметри)
3. Створи `ResponseData/Group/MethodResponse.php extends Data`
4. Якщо відповідь містить колекцію — використай `normalizeCollection()` + `DataCollectionOf`
5. Напиши тест у `tests/Group/MethodTest.php` з `Http::fake()` і реальним XML прикладом

---

## Інтеграція в проект A20

Пакет використовується через:
- `app/Services/NovaPay/` або подібний сервіс-шар
- JWT токен зберігається між запитами (DB або cache)
- При `NovapayApiException` → retry логіка або alert

Пов'язані задачі в A20:
- `TASK-np-payment-registry.md` — імпорт реєстрів виплат НП з цим пакетом
