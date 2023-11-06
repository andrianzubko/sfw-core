<?php

declare(strict_types=1);

namespace SFW\Event;

/**
 * Emits after current(!) transaction is successfully rolled back.
 *
 * Listener will be ignored if provided outside of transaction.
 */
class TransactionRolledBack extends \SFW\Event {}
