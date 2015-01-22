# SiteFramework
Site Framwork for PHP. Content management, templating and modular authentication.

This PHP framework is to help make quick but clean site deployments. It features detailed logging, a template system, multiple language support, domain to language mapping and maybe most importantly (or most developed at least) a modular authentication system.

The auth system is built to perform authentication first, and always. However, only show login prompt if needed by RequireLogin(). It's modular, making it easy to create new authentication mechanisms. It's also separate from profile. Essentially means that multiple authentication mechanisms can be used for the same profile (i.e login via Facebook or Active Directory to your, same, profile).

This package also features upload file handling, and yet another MySQL abstraction class. This one takes a different approach to protecting against SQL-injection, simply by letting every parameter being a separate argument to the q() -method.

Give it a try, let me know if you like it or if not - what you didn't.

License: GPL3

Simon Fredriksson
