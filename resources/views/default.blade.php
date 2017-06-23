<!DOCTYPE html>
<html>
  <head>
    <title>{{ config('projdoc.name') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <link rel="stylesheet" href="https://cdn.rawgit.com/thomaspark/bootswatch/v4/materia/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/atom-one-light.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <style type="text/css">
      *[id]:before {
        display: block;
        content: " ";
        margin-top: -75px;
        height: 75px;
        visibility: hidden;
      }
    </style>
  </head>
  <body class="pb-5">
    <nav class="navbar navbar-toggleable-md navbar-inverse bg-primary sticky-top mb-5">
      <a class="navbar-brand" href="{{ $meta['base'] }}">
        {{ config('projdoc.name') }}
      </a>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="{{ $meta['base'] }}">Home</a>
          </li>
        </ul>
      </div>
    </nav>
    <main class="container">
      {!! $content !!}
    </main>
    <div class="container-fluid fixed-bottom bg-faded">
      <div class="row">
        <div class="col text-center py-2">
          <a target="_blank" href="https://github.com/MDM23/projdoc" class="text-muted">
            Powered by Projdoc
          </a>
        </div>
      </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
  </body>
</html>
