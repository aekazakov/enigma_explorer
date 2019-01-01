<!DOCTYPE html>
<html>
<head>
  <title>Enigma::@yield('title')</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/css/loading.css" />
  <link rel="stylesheet" type="text/css" href="/css/typeaheadjs.css" />
  <script src="/js/jquery-3.3.1.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
  <style>
    /* set mark padding 0 */
    mark {
      padding: 0;
    }
    a {
      text-decoration: none;
    }
  </style>
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
          <a class="nav-link" href="/index" id="mainLink">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/isolates" id="isolatesLink">Isolates</a>
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
          <a class="nav-link" href="https://github.com/RainLiuX/enigma_docs/blob/master/api.md" id="apiLink">API</a>
        </li>
      </ul>
      <!-- redirect to the first page of search -->
      <form class="form-inline my-2 my-lg-0" id="searchForm" action="#">
        <input style="display:none;" />
        <input name="keyword" class="typeahead form-control mr-sm-2" id="searchInput" autocomplete="off" type="search" data-provide="typeahead" placeholder="Enter keywords" aria-label="Search" />
        <button class="btn btn-outline-primary my-2 my-sm-0" id="searchButton" type="button">Search</button>
      </form>
    </div>
  </nav>
  <div class="container">
    @yield('content')
  </div>
  <script src="/js/typeahead.bundle.js"></script>
  <script>
    $(document).ready(function() {
      $('#{{ $activeLink }}').addClass('active');

      // ajax form submission
      function conditionRedirect() {
        var keyword = encodeURI($('#searchInput').val());

        // examine number of results
        $.get('/api/v1/isolates/count/'+keyword, function(data) {
          var reNum = data.count;
          // if only 1 result, go to detail page
          if (parseInt(reNum) == 1) {
            $.get('/api/v1/isolates/keyword/'+keyword,function(data) {
              window.location.href = '/isolates/id/'+data[0].id;
            });
          } else {
            window.location.href = '/search?keyword='+keyword;
          }
        });
      }; 
      
      $('#searchButton').click(conditionRedirect);
      $('#searchInput').bind('keypress', function(event) {
        if (event.keyCode == '13') {
          conditionRedirect();
        }
      });

      // typeahead (search hint)
      $('#searchInput').typeahead({
          highlight: true,
          minLength: 3
        }, {
          limit: 10,
          source: function(query, processSync, processAsync) { 
            return $.get('/api/v1/isolates/hint/' + encodeURI(query), function(data) {
              return processAsync(data);
            });
          }
        }
      );
      // hack to avoid typeahead break inline-form
      $('.twitter-typeahead').css('width', "74.5%");
    });
  </script>
</body>
</html>
