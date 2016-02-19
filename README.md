# Librato Widget

## Config

```
-
    id: librato1
    provider: \StpBoard\Librato\LibratoControllerProvider
    refresh: 60
    width: 4
    params:
      name: NAME_TO_BE_DISPLAYED
      appUser: LIBRATO_APP_USER
      apiToken: LIBRATO_API_TOKEN
      action: ACTION
      begin: -30minutes
```

### Available actions are:

* rpm
* average_response_time
* error_rate

Parameter ```begin``` is optional - default value is ```-30minutes```. It describes time period from which data should
be displayed.
