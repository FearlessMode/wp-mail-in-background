Queues emails sent by `wp_mail`, so they are processed in a fake background queue (using WP Cron).

Right now, this needs the default `wp_mail` function to be plugged with the following change.

Find this line:

```php
$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
```

Add this line right after.

```php
// a plugin short-circuited this email
if( empty( $atts ) ) {
	return false;
}
```

Hopefully, [#35069](https://core.trac.wordpress.org/ticket/35069) gets merged into WordPress core to make this a bit easier.