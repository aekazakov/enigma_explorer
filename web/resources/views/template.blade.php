<!DOCTYPE html>
<html>
<head>
  <title>Enigma::@yield('title')</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">ENIGMA</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="nav navbar-nav w-50 nav-justified mx-auto">
        <li class="nav-item">
          <a class="nav-link" href="index" id="mainLink">Main</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="isolates" id="isolatesLink">Isolates</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="communitiesLink">Communities</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="interactionLink">Interactions</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="enrichmentLink">Enrichments</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="sitemapLink">Sitemap</a>
        </li>
      </ul>
      <form class="form-inline my-2 my-lg-0" action="search" method="get">
        <input name="keyword" class="form-control mr-sm-2" type="search" placeholder="Enter keywords" aria-label="Search">
        <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Search</button>
      </form>
    </div>
  </nav>
  <div class="container">
    @yield('content')
  </div>
  <script>
  $(document).ready(function() {
    $('#{{ $activeLink }}').addClass('active');
  });
  </script>
</body>
</html>
