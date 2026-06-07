<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\EntryRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;

final class EntryCreateHandler
{
    private const NAME_MAX_LENGTH = 100;
    private const MESSAGE_MAX_LENGTH = 500;

    public function __construct(private readonly EntryRepository $entryRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface|PromiseInterface
    {
        $body = (array) $request->getParsedBody();

        $name = trim((string) ($body['name'] ?? ''));
        $message = trim((string) ($body['message'] ?? ''));

        $error = $this->validate($name, $message);

        return $error ?? $this->entryRepository->create($name, $message)
            ->then(fn() => new Response(302, ['Location' => '/']));
    }

    private function validate(string $name, string $message): ?ResponseInterface
    {
        if ($name === '' || $message === '') {
            return $this->validationError('Name and message are required.');
        }

        if (strlen($name) > self::NAME_MAX_LENGTH) {
            return $this->validationError('Name must be at most ' . self::NAME_MAX_LENGTH . ' characters.');
        }

        if (strlen($message) > self::MESSAGE_MAX_LENGTH) {
            return $this->validationError('Message must be at most ' . self::MESSAGE_MAX_LENGTH . ' characters.');
        }

        return null;
    }

    private function validationError(string $message): ResponseInterface
    {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return Response::html(<<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Guestbook — validation error</title>
            </head>
            <body>
                <h1>Could not sign the guestbook</h1>
                <p>$safeMessage</p>
                <p><a href="/">Back to guestbook</a></p>
            </body>
            </html>
            HTML)->withStatus(422);
    }
}
