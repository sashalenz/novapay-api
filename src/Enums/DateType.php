<?php

namespace Sashalenz\NovapayApi\Enums;

enum DateType: int
{
    case PaymentDate = 0;  // Дата проведення
    case FromDate = 1;     // Дата «від»
    case CreatedDate = 2;  // Дата створення
    case UpdatedDate = 3;  // Дата оновлення статусу
}
