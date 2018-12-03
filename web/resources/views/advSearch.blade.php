@extends('template')

@section('title', 'Advance Search')

@section('content')
    <h1 class="h1 my-5 text-white">Advance Search</h1>
    <p class="p-3 text-left text-white" style="font-size:1.3em;">
      Search for items that <em>exactly</em> or <em>roughly</em> match a series of conditions
    </p>
    <hr />
    <form id="mainForm" action="#" method="POST">
      <div class="form-group row my-4">
        <label for="isoidForm" class="text-white col-form-label col-sm-12 col-md-3 text-center">Isolate ID</label>
        <div class="col-sm-9 col-md-8">
          <input type="text" aria-label="Isolate ID" class="form-control" id="isoidForm" name="isoid" placeholder="Isolate ID" />
        </div>
        <div class="col-sm-3 col-md-1">
          <button type="button" class="btn btn-outline-light checkButton" id="isoidCheck">=</button>
        </div>
      </div>
      <div class="form-group row my-4">
        <label for="orderForm" class="text-white col-form-label col-sm-12 col-md-3 text-center">Phylogenic Order</label>
        <div class="col-sm-9 col-md-8">
          <input type="text" aria-label="Phylogenic Order" class="form-control" id="orderForm" name="order" placeholder="Phylogenic Order" />
        </div>
        <div class="col-sm-3 col-md-1">
          <button type="button" class="btn btn-outline-light checkButton" id="orderCheck">=</button>
        </div>
      </div>
      <div class="form-group row my-4">
        <label for="relativeForm" class="text-white col-form-label col-sm-12 col-md-3 text-center">Closest Reative</label>
        <div class="col-sm-9 col-md-8">
          <input type="text" aria-label="Closest Relative" class="form-control" id="relativeForm" name="relative" placeholder="Closest Relative" />
        </div>
        <div class="col-sm-3 col-md-1">
          <button type="button" class="btn btn-outline-light checkButton" id="relativeCheck">~</button>
        </div>
      </div>
      <div class="form-group row my-4">
        <label for="labForm" class="text-white col-form-label col-sm-12 col-md-3 text-center">Lab Isolated</label>
        <div class="col-sm-9 col-md-8">
          <input type="text" aria-label="Lab Isolated" class="form-control" id="labForm" name="lab" placeholder="Lab Isolated" />
        </div>
        <div class="col-sm-3 col-md-1">
          <button type="button" class="btn btn-outline-light checkButton" id="labCheck">~</button>
        </div>
      </div>
      <button type='button' class="btn btn-outline-light btn-lg float-right" id="submitButton">Submit</button>
    </form>
    
    <script src='/js/jquery.redirect.js'></script>
    <script>
      $(document).ready(function() {
        $('body').addClass('bg-secondary');
      });

      // describe checkButton behaviour
      $('.checkButton').click(function() {
        console.log('checkButton clicked');
        if ($(this).html() == '=') {
          $(this).html('~');
        } else if ($(this).html() == '~') {
          $(this).html('=');
        }
      });

      // submit form through ajax
      $('#submitButton').click(function() {
        // construct object from form data
        var formArray = $('#mainForm').serializeArray();
        var formObj = {};
        for (i=0; i<formArray.length; i++) {
          formObj[formArray[i]['name']] = formArray[i]['value'];
        };
        var checkList = Object.keys(formObj);
        var checkObj = {};
        for (i=0; i<checkList.length; i++) {
          checkObj[checkList[i]] = ($('#'+checkList[i]+'Check').html() == '=');
        };
        formObj['isEqual'] = checkObj;

        // post form object
        $.redirect('/advSearch/1', formObj, 'POST');    
      });
    </script>
@endsection
