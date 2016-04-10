CRM.$(function ($) {
  // Note: all params accepted by the php addEntityRef also work here
  // For example, select organization with button to add a new one:
  $('[name=volunteer_project]').crmEntityRef({
    entity: 'volunteer_project',
  });
});
