<?php

return array(
    'status' => array(
        0 => 'New lead',          // новый лид в системе
        1 => 'Operator',          // лид обрабатывается оператором
        2 => 'Operator bad',      // оператор отметил лид как bad
        3 => 'Auction',           // лид на аукционе
        4 => 'Close auction',     // лид снят с аукциона
        5 => 'Agent bad',         // агент пометил лид как bad
        6 => 'Closed deal',       // закрытие сделки по лиду
        7 => 'Selective auction', // добавление лида на аукционы выборочных агентов
        8 => 'Private group',      // лид для приватной группы
    ),
    'auctionStatus' => array(
        0 => 'Not at auction',          // не на аукциоа
        1 => 'Auction',                 // на аукционе

        2 => 'Closed by max open',      // снят с аукциона по причине максимального открытия лидов
        3 => 'Closed by time expired',  // снят с аукциона по причине истечения времени по лиду
        4 => 'Closed by agent bad',     // снят с аукциона, большая часть агентов пометили его как bad
        5 => 'Closed by close deal',    // снят с аукциона по закрытию сделки по лиду
        6 => 'Private group',           // лид для приватной группы в аукционе не учавствует
    ),
    'paymentStatus' => array(
        0 => 'Expects payment',          // ожидание платежа по лиду
        1 => 'Payment to depositor',     // оплата депозитору его доли по лиду
        2 => 'Payment for unsold lead',  // "штраф" депозитору за непроданный лид
        3 => 'Payment for bad lead',     // оплата агентам по плохому лиду (возврат покупателям, штраф депозитору)
        4 => 'Private group',            // лид для приватной группы в денежной системе не учавствует
    ),
    'specifications' => array(
        1 => 'For dealmaker'
    )
);