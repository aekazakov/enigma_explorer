<form class="my-5" id="mainSearchForm" action="#">
  <input style="display:none;" />
  <div class="form-group row">
    <div class="col-sm-12 col-lg-6" id="inputWrapper">
      <input name="keyword" class="typeahead form-control align-baseline my-2" id="mainSearchInput" type="search" data-provide="typeahead" autocomplete="off" placeholder="{{ $slot }}" aria-label="Search" />
    </div>
    <div class="col-sm-6 col-lg-2">
      <button class="btn btn-outline-primary my-2 d-block mx-auto" id="mainSearchButton" type="button">Search</button>
    </div>
    <div class="col-sm-6 col-lg-2">
      <a href="/advSearch" class="btn btn-outline-secondary my-2 d-block mx-auto">Advance Search</a>
    </div>
    <div class="col-sm-6 col-lg-2">
      <a href="/browse" class="btn btn-outline-success my-2 d-block mx-auto">Browse</a>
    </div>
  </div>
</form>

<script src="/js/typeahead.bundle.js"></script>
<script>
  $(document).ready(function() {
    // ajax form submission
    function conditionRedirect() {
      var keyword = encodeURI($('#mainSearchInput').val());

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
    
    $('#mainSearchButton').click(conditionRedirect);
    $('#mainSearchInput').bind('keypress', function(event) {
      if (event.keyCode == '13') {
        conditionRedirect();
      }
    });

    // search hint
    $('#mainSearchInput').typeahead({
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
    // hack to make the inputbox 100%
    // It seems the hack will ruin hintbox
    //$('.twitter-typeahead').attr('style', 'width:100% !important;');
  });
</script>
