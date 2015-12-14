Queues emails sent by `wp_mail`, so they are processed in a fake background queue (using WP Cron).

Hopefully, [#35069](https://core.trac.wordpress.org/ticket/35069) gets merged into WordPress core to speed this up even more.