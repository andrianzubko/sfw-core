<?php

declare(strict_types=1);

namespace SFW\Event;

use SFW\Event;

/**
 * Emits after current(!) transaction is successfully committed.
 *
 * Listener will be ignored if provided outside of transaction.
 */
class TransactionCommitted extends Event {}
