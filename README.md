# NovaPay API

Laravel пакет для роботи з [NovaPay Business Cabinet API](https://business.novapay.ua/) v2.0.

Реалізовано всі 15 методів згідно офіційної документації «API інструкція (березень 2026)».

---

## Встановлення

```bash
composer require sashalenz/novapay-api
```

Опублікуй конфіг:

```bash
php artisan vendor:publish --tag="novapay-api-config"
```

---

## Конфігурація

Додай у `.env`:

```dotenv
NOVAPAY_LOGIN=your_login
NOVAPAY_REFRESH_TOKEN=your_refresh_token
NOVAPAY_PUBLIC_CERTIFICATE="-----BEGIN RSA PUBLIC KEY-----\n...\n-----END RSA PUBLIC KEY-----"

# Необов'язково
NOVAPAY_API_URL=https://business.novapay.ua/Services/ClientAPIService.svc
NOVAPAY_TIMEOUT=30
```

`refresh_token` і `public_certificate` генеруються в особистому кабінеті:
**Бізнес-кабінет → Система → Налаштування → API**.

---

## Автентифікація

### JWT (рекомендований метод)

Метод використовує одноразову ротацію токенів. Кожен виклик повертає **новий** `refresh_token` і `public_certificate`, які треба зберігати для наступного запиту.

```php
use Sashalenz\NovapayApi\NovapayApi;

// З конфігу (.env)
$auth = NovapayApi::auth()->jwtFromConfig();

// Або явно
$auth = NovapayApi::auth()->jwt(
    refreshToken: 'REFRESH_TOKEN',
    login: 'your_login',
    publicCertificate: '-----BEGIN RSA PUBLIC KEY-----...',
);

$jwt              = $auth->jwt;               // токен для API запитів
$newRefreshToken  = $auth->refresh_token;     // зберегти для наступного разу
$newCertificate   = $auth->public_certificate; // зберегти для наступного разу
$expiration       = $auth->expiration;
```

> ⚠️ Після кожного успішного `jwt()` старий `refresh_token` анульовується. Зберігай нові значення до своєї БД/кешу.

### Легасі (буде відключено 31.08.2026)

```php
// Крок 1 — логін + пароль
$pre = NovapayApi::auth()->preAuthenticate(
    login: 'your_login',
    password: 'your_password',
);

// Крок 2 — підтвердження OTP
$session = NovapayApi::auth()->authenticate(
    tempPrincipal: $pre->temp_principal,
    codeOperationOtp: $pre->getCodeOperationOtp(),
    otpPassword: '123456',
);

$principal = $session->principal;

// Продовження сесії
$refreshed = NovapayApi::auth()->refresh($principal);
$newPrincipal = $refreshed->new_principal;
```

---

## Методи API

Всі методи, окрім Auth, приймають токен через fluent-методи:

```php
->withJwt(string $jwt)         // рекомендовано
->withPrincipal(string $p)     // легасі
```

### Клієнти (підприємства)

#### `GetClientsList` — список підприємств

```php
$response = NovapayApi::clients()
    ->withJwt($jwt)
    ->list();

foreach ($response->clients as $client) {
    echo $client->id;         // int
    echo $client->name;       // string
    echo $client->statecode;  // ЄДРПОУ / РНОКПП
}
```

---

### Рахунки

#### `GetAccountsList` — список рахунків підприємства

```php
$response = NovapayApi::accounts()
    ->withJwt($jwt)
    ->list(clientId: 8);

foreach ($response->accounts as $account) {
    echo $account->id;          // int
    echo $account->IBAN;        // UA29358710...
    echo $account->currency;    // UAH
    echo $account->statuscode;  // AccountStatus enum
    echo $account->statuscode->label(); // 'Активний'
}
```

#### `GetAccountRest` — баланс рахунку

```php
$response = NovapayApi::accounts()
    ->withJwt($jwt)
    ->balance(accountId: 49);

echo $response->confirmed_balance;  // float — підтверджений залишок
echo $response->available_balance;  // float — доступний залишок
echo $response->projected_balance;  // float — прогнозований залишок
```

#### `GetPaymentsList` — виписка платежів (XML)

```php
$response = NovapayApi::accounts()
    ->withJwt($jwt)
    ->payments(
        accountId: 49,
        dateFrom: '01.10.2024',   // dd.mm.yyyy
        dateTo: '31.10.2024',
        dateType: DateType::PaymentDate, // enum: 0-3
    );

// Рядок XML з CDATA
$rawXml = $response->payments;

// Або розпарсити у масив
$payments = $response->parsePayments();
foreach ($payments as $p) {
    echo $p['Amount'];
    echo $p['Purpose'];
    echo $p['StatusDocument']; // New, Processed, ...
}
```

#### `GetAccountExtract` — виписка з залишками (XML)

```php
$response = NovapayApi::accounts()
    ->withJwt($jwt)
    ->extract(
        accountId: 49,
        dateFrom: '01.10.2024',
        dateTo: '31.10.2024',
    );

$data = $response->parseExtract();
// $data['head']     — масив заголовків (ExtractHead)
// $data['payments'] — масив транзакцій (Docs)
```

#### `GetAccountTurns` — обороти по рахунку

```php
$response = NovapayApi::accounts()
    ->withJwt($jwt)
    ->turns(
        accountId: 49,
        dateFrom: '01.10.2024',
        dateTo: '31.10.2024',
    );

foreach ($response->turns as $turn) {
    echo $turn->date;         // '14.10.2024'
    echo $turn->IBAN;
    echo $turn->InRest;       // ?float — залишок на початок
    echo $turn->CrncyDebit;   // ?float — дебет
    echo $turn->CrncyCredit;  // ?float — кредит
    echo $turn->CrncyRest;    // ?float — залишок на кінець
}
```

---

### Платежі

#### `CreatePayment` — створення платежу

```php
$response = NovapayApi::payments()
    ->withJwt($jwt)
    ->create([
        'account_id'      => 49,
        'OrgDate'         => '14.11.2024',       // dd.mm.yyyy
        'CreditCodeIBAN'  => 'UA29358700000067320000000001',
        'CreditName'      => 'ФОП Тест Тестовий',
        'CreditStateCode' => '1234567899',
        'Amount'          => '1250.00',
        'CurrencyTag'     => 'UAH',
        'Purpose'         => 'Оплата за товар згідно рахунку №42',
        // Необов'язково:
        'CreditCountry'      => 'UA',
        'CreditNotResident'  => false,
    ]);

if ($response->isSuccessful()) {
    // Платіж прийнято
}
```

#### `GetRecallPaymentsList` — платежі доступні для відкликання

```php
$response = NovapayApi::payments()
    ->withJwt($jwt)
    ->recallableList();

foreach ($response->payments as $payment) {
    echo $payment->id;          // int
    echo $payment->orgdate;
    echo $payment->amount;      // ?float
    echo $payment->debitIBAN;
    echo $payment->creditIBAN;
    echo $payment->purpose;
}
```

#### `RecallPayment` — відкликання платежу

```php
$response = NovapayApi::payments()
    ->withJwt($jwt)
    ->recall(
        paymentId: 720,
        info: 'Відкликання через API',  // необов'язково
    );

echo $response->isSuccessful(); // bool
```

---

### Реєстри (Post-payment / КО)

#### `GetRegister` — генерація виписки реєстру

```php
use Sashalenz\NovapayApi\Enums\RegisterFileExtension;

$response = NovapayApi::registers()
    ->withJwt($jwt)
    ->get(
        clientId: 8,
        from: '01.05.2025',
        into: '21.05.2025',
        fileExtension: RegisterFileExtension::XLS, // або CSV
    );

$statementId = $response->statement_id; // зберегти для download()
```

#### `DownloadRegister` — отримання посилання на файл

```php
$response = NovapayApi::registers()
    ->withJwt($jwt)
    ->download(id: $statementId);

echo $response->status;    // 'created' — файл готовий
echo $response->url;       // https://...novapay.ua/files/...xlsx
echo $response->file_name; // 'Реєстр переказів з 2025-05-01 до 2025-05-21.xlsx'
echo $response->file_type; // 'XLS'
```

> 💡 Файл може генеруватись асинхронно. Якщо `status !== 'created'` — викликай `download()` повторно через кілька секунд.

---

## Proxy

Всі ApiModel підтримують проксі:

```php
NovapayApi::accounts()
    ->withJwt($jwt)
    ->proxy('http://proxy.example.com:8080')
    ->balance(49);
```

---

## Обробка помилок

Всі методи кидають `NovapayApiException` при HTTP помилці або невалідній SOAP відповіді.

Логічні помилки (невірний токен, нема доступу) повертаються як успішна HTTP відповідь з `result = 'error'`:

```php
use Sashalenz\NovapayApi\Exceptions\NovapayApiException;

try {
    $response = NovapayApi::accounts()
        ->withJwt($jwt)
        ->balance(accountId: 49);

    if (! $response->isSuccessful()) {
        // Логічна помилка API
        $error = $response->error;       // ?ApiError
        logger()->warning($error?->title, ['status' => $error?->status]);
        return;
    }

    // Успіх
    echo $response->confirmed_balance;

} catch (NovapayApiException $e) {
    // HTTP або SOAP помилка
    logger()->error('NovaPay API error', ['message' => $e->getMessage()]);
}
```

---

## Enums

| Клас | Значення |
|---|---|
| `AccountStatus` | `Active`, `Closed`, `OnApproval`, `Arrested` |
| `DateType` | `PaymentDate(0)`, `FromDate(1)`, `CreatedDate(2)`, `UpdatedDate(3)` |
| `RegisterFileExtension` | `XLS`, `CSV` |

```php
use Sashalenz\NovapayApi\Enums\AccountStatus;

AccountStatus::Active->label();      // 'Активний'
AccountStatus::Active->statusCode(); // 1
```

---

## Тести

```bash
composer test
```

Всі тести використовують `Http::fake()` — реальні запити до NovaPay не виконуються.

---

## Ліцензія

MIT
