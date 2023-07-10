<?php

namespace App\Notify;

class Example extends \SFW\Notify
{
    /**
     * Fetching data from database.
     *
     * If transaction aborted, all notifies, called at this transaction, will be destroyed.
     */
    public function __construct(protected string $email, protected string $message) {}

    /**
     * Build and return array of structures.
     *
     * This method called after browser disconnect.
     */
    public function build(\SFW\NotifyStruct $defaultStruct): array
    {
        $structs = [];

        $struct = clone $defaultStruct;

        $struct->subject = 'Example message';

        $struct->sender = self::$config['my']['notifier']['sender'];

        $struct->recipients[] = $this->email;

        $struct->replies = self::$config['my']['notifier']['replies'];

        $struct->e['message'] = $this->message;

        $struct->body = $this->sys('Templater')->transform($struct->e, '.message.example.php');

        $structs[] = $struct;

        return $structs;
    }
}
