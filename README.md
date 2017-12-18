![Waddle's Logo](public/logo.png)

**_This is a prototype, therefore highly experimental_.**
# About Waddle

Waddle is a simple Trello-like app for organizing your mail box. It was originally a project for a Programming class, and its currently builded with:
- PHP, Slim micro-framework and php-imap library
- Vue.js progressive framework and jQuery to everything JS related
- Foundation for Sites framework for everything else (actually is not quite useful here yet)

## Get Started

Check if you're running PHP 7, and if your web server supports mod_rewrite or something similar. Older PHP versions _might_ be supported but not guaranteed since I'm not testing with them.

Install composer dependencies (if you don't have it [download composer](https://getcomposer.org/download/)):
```
composer install
```

Start command-line server with:
```
composer start
```

Or with that (change localhost:8080 to whatever servername and port you want):

```
php -S localhost:8080 -t public/index.php
```

Or point the server's root to the `public/` directory.

## Contributing

I'm opened to contributions, feel free to suggest something. Check the current opened project at [Waddle's Board](https://github.com/germanocorrea/waddle/projects/1). Discussions about anything described in the board should lead to (new or not new) issues created from the respective card. You can debug the back-end using [Monolog](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md).

## Thanks

- My classmate Laura, which originally created the app with me
