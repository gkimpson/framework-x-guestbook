<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\Entry;
use App\Repository\EntryRepository;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;

final class EntryListHandler
{
    public function __construct(private readonly EntryRepository $entryRepository)
    {
    }

    public function __invoke(): PromiseInterface
    {
        return $this->entryRepository->all()
            ->then(fn(array $entries) => Response::html($this->render($entries)));
    }

    /**
     * @param list<Entry> $entries
     */
    private function render(array $entries): string
    {
        if ($entries === []) {
            $items = '<li><em>No entries yet. Be the first to sign!</em></li>';
        } else {
            $items = '';
            foreach ($entries as $entry) {
                $name = htmlspecialchars($entry->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $message = htmlspecialchars($entry->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $when = $entry->createdAt?->format('Y-m-d H:i') ?? '';
                // Would have used an HTML template engine normally (Twig, Blade) but feels overkill for this task
                $items .= <<<HTML
                    <li>
                        <strong>$name</strong>
                        <time>$when</time>
                        <p>$message</p>
                    </li>
                HTML;
            }
        }

        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Guestbook</title>
            </head>
            <body>
                <h1>Guestbook</h1>

                <form method="post" action="/entries">
                    <p><input type="text" name="name" placeholder="Your name" maxlength="100" required></p>
                    <p><textarea name="message" placeholder="Your message" maxlength="500" required></textarea></p>
                    <p><button type="submit">Sign Guestbook</button></p>
                </form>

                <ul>$items</ul>
            </body>
            </html>
            HTML;
    }
}
