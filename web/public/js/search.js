// Fill a empty data view
function emptyDataView() {
  $('#dataList').append(`
    <div class="list-group-item">
      <p><strong>
        Nothing found. Try another keyword?
      </p></strong>
    </div>
  `);
};

// Fill data view with error info
function errorDataview(errorHint) {
  $('#resNumber').html('0');
  $('#dataList').append(`
    <div class="list-group-item">
      <p><strong>
        ${errorHint}
      </p></strong>
    </div>
  `);
};

// Generate pagination
function genPagination(data, page, keyword) {
  // count how may page is needed 
  var pageNum = Math.ceil(data.length / PER_PAGE);

  // generate pagination
  var pageStart, pageEnd;
  if (pageNum <= MAX_PAGE) {
    pageStart = 1;
    pageEnd = pageNum;
  } else if (page < MAX_PAGE) {
    pageStart = 1;
    pageEnd = MAX_PAGE;
  } else if (pageNum - page < MAX_PAGE - 1) {
    pageStart = pageNum - MAX_PAGE + 1;
    pageEnd = pageStart + MAX_PAGE -1;
  } else {
    pageStart = page - Math.floor(MAX_PAGE/2);
    pageEnd = pageStart + MAX_PAGE -1;
  }
  for (let i = pageStart; i <= pageEnd; i++) {
    $('#nextPage').before(`
      <li class="page-item" id="pageItem${i}">
        <a class="page-link" id="pageLink${i}">${i}</a>
      </li>
    `);
    $(`#pageLink${i}`).click(function() {
      bindPagination(data, i, keyword);
    });
  }
  $("#pageLinkFirst").unbind("click").click(function() {
    bindPagination(data, 1, keyword);
  });
  $("#pageLinkPrev").unbind("click").click(function() {
    bindPagination(data, page-1, keyword);
  });
  $("#pageLinkNext").unbind("click").click(function() {
    bindPagination(data, page+1, keyword);
  });
  $("#pageLinkLast").unbind("click").click(function() {
    bindPagination(data, pageNum, keyword);
  });

  // assign active page item
  $(`#pageItem${page}`).addClass('active');
  // disable previous or next link if needed
  if (page == 1) {
    $('#prevPage').addClass('disabled');
    $('#firstPage').addClass('disabled');
  }
  if (page == pageNum) {
    $('#nextPage').addClass('disabled');
    $('#lastPage').addClass('disabled');
  }
  if (page != 1) {
    $('#prevPage').removeClass('disabled');
    $('#firstPage').removeClass('disabled');
  }
  if (page != pageNum) {
    $('#nextPage').removeClass('disabled');
    $('#lastPage').removeClass('disabled');
  }

  // scroll to the top
  $("html, body").animate({ scrollTop: 0 }, "fast");
};

// bind pagenination pageLinks to functions
function bindPagination(data, page, keyword) {
  $('#dataList').empty();
  appendDataView(data, page, keyword);
  $('.page-item[id^=pageItem]').remove();
  genPagination(data, page, keyword);
}
