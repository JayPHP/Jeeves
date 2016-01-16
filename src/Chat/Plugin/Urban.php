<?php declare(strict_types=1);

namespace Room11\Jeeves\Chat\Plugin;

use Room11\Jeeves\Chat\Client\Xhr as ChatClient;
use Room11\Jeeves\Chat\Command\Message;

class Urban implements Plugin
{
    const COMMAND = 'urban';

    private $chatClient;

    public function __construct(ChatClient $chatClient)
    {
        $this->chatClient = $chatClient;
    }

    public function handle(Message $message): \Generator
    {
        if (!$this->validMessage($message)) {
            return;
        }

        yield from $this->getResult($message);
    }

    private function validMessage(Message $message): bool
    {
        if (get_class($message) !== 'Room11\Jeeves\Chat\Command\Command') {
            return false;
        }
    }

    private function getResult(Message $message): \Generator
    {
        $response = yield from $this->chatClient->request(
            'http://api.urbandictionary.com/v0/define?term=' . rawurlencode(implode('%20', $message->getParameters()))
        );

        $result = json_decode($response->getBody(), true);

        yield from $this->chatClient->postMessage($this->getMessage($result));
    }

    private function getMessage(array $result): string
    {
        return sprintf(
            '[ [%s](%s) ] %s',
            $result['list'][0]['word'],
            $result['list'][0]['permalink'],
            str_replace("\r\n", ' ', $result['list'][0]['definition'])
        );
    }
}
