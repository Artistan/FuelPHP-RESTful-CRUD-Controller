Installation
============
Copy to a subfolder of fuel/packages. Add `rest_crud` to `['always_load']['packages']` in config.php.

Usage
=====
For the most part, HTTP verbs map directly to functions (e.g. GET /controllername => function get()).
The client can choose the return format by specifying an extension (see the documentation for Controller_Rest in
FuelPHP).

GET requests can invoke one of three methods.

* When one or more arguments are specified, it will try to invoke `{Input::method}_{resource1}`.
* Otherwise it will invode {Input::method}_index();
* When no arguments are specified, it will try to invoke `get_list($page)` (falling back to `get(...)` or get_index).
* When the search GET paramater is populated, it will invoke `get_search($query, $page)`, falling back
  to `get_list($page)`, and finally `get(...)` or get_index.
