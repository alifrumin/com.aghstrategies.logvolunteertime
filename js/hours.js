CRM.$(function ($) {

  $('#volunteer_project_select').change(function () {
    console.log('Handler for .change() called.');
    var $ProjectId = $('#volunteer_project_select').val();
    console.log($volProjectId);
  });

  CRM.api3('VolunteerNeed', 'get', {
    projectId: 1,
  }).done(function (result) {
      var needs = result.values;
      var select = $("<select id=\"selectId\" name=\"selectName\" />");
      $.each(needs, function () {
        $('<option />', { value: this.id, text: this.role_label }).appendTo(select);
      });

      select.appendTo('div.crm-section:nth-child(6)');
    });
});
