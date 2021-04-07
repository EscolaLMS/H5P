---
title: Framework agnostic PHP
weight: 2
---

In framework agnostic projects you can use this template as [the ray config file](/docs/ray/v1/configuration/general).

```php
// save this in a file called "ray.php"
<?php
return [
    /*
    * This settings controls whether data should be sent to Ray.
    */
    'enable' => true,
    
    /*
     *  The host used to communicate with the Ray app.
     */
    'host' => 'localhost',

    /*
     *  The port number used to communicate with the Ray app. 
     */
    'port' => 23517,
    
    /*
     *  Absolute base path for your sites or projects in Homestead, Vagrant, Docker, or another remote development server.
     */
    'remote_path' => null,
    
    /*
     *  Absolute base path for your sites or projects on your local computer where your IDE or code editor is running on. 
     */
    'local_path' => null,
    
    /*
     * When this setting is enabled, the package will not try to format values sent to Ray.
     */
    'always_send_raw_values' => false,
];
```
