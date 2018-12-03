<form class="my-5" id="mainSearchForm" action="#">
  <input style="display:none;" />
  <div class="form-group row">
    <div class="col-sm-12 col-lg-8">
      <input name="keyword" class="form-control align-baseline my-2" id="mainSearchInput" type="search" placeholder="{{ $slot }}" aria-label="Search" />
    </div>
    <div class="col-sm-6 col-lg-2">
      <button class="btn btn-outline-primary my-2 d-block mx-auto" id="mainSearchButton" type="button">Search</button>
    </div>
    <div class="col-sm-6 col-lg-2">
      <button class="btn btn-outline-secondary my-2 d-block mx-auto" id="advSearchButton" type="button">Advance Search</button>
    </div>
  </div>
</form>
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
          window.location.href = '/search/1?keyword='+keyword;
        }
      });
    }; 
    
    $('#mainSearchButton').click(conditionRedirect);
    $('#mainSearchInput').bind('keypress', function(event) {
      if (event.keyCode == '13') {
        conditionRedirect();
      }
    });
  });
</script>
