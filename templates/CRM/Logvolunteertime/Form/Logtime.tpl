<div class="crm-project-id-{$vid} crm-block crm-volunteer-signup-form-block">
  {include file="CRM/UF/Form/Block.tpl" fields=$volunteerProfile}
  {if $form.volunteer_role_id}
  <div class="crm-section volunteer_role-section">
    <div class="label">{$form.volunteer_role_id.label}</div>
    <div class="content">{$form.volunteer_role_id.html}</div>
  </div>
  <div class="crm-section volunteer_shift-section">
    <div class="label">Volunteer Shift</div>
    <div class="content">{$form.volunteer_need_id.html}</div>
  </div>
  {/if}
  <div class="crm-section other_role-section">
    <div class="label">{$form.other_role.label}</div>
    <div class="content">{$form.other_role.html}</div>
  </div>
  <div class="crm-section volunteer_start-date">
    <div class="label">{$form.new_start_date.label}</div>
    <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=new_start_date}</div>
  </div>
  <div class="crm-section volunteer_scheduled">
    <div class="label">{$form.scheduled_duration.label}</div>
    <div class="content">{$form.scheduled_duration.html}<p>Input time in minutes</p></div>
  </div>
  <div class="crm-section volunteer_actual">
    <div class="label">{$form.actual_duration.label}</div>
    <div class="content">{$form.actual_duration.html}<p>Input time in minutes</p></div>
  </div>
  <div class="crm-section volunteer_details-section">
    <div class="label">{$form.details.label}</div>
    <div class="content">{$form.details.html}</div>
  </div>

  <div>
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{literal}
<script>

  function toggleShiftSelection(tog) {
    if (tog === 0) {
      cj('#volunteer_need_id').val('');
    } else {

    }
  }

  function filterShifts(event) {
    var selected_role = cj('#volunteer_role_id').val();
    //cj('#volunteer_need_id').empty();
    //var shifts = event.data;
    //shifts.each(function() {
      //if (selected_role == cj(this).data('role')) {
        //cj('#volunteer_need_id').append(cj(this));
        //}
        //});
        // if there are no shift options, hide the shift select box altogether
        //var shift_count = cj('#volunteer_need_id option').length;
        //toggleShiftSelection(shift_count);
        cj(".volunteer_shift-section").show();
      }


      if(cj("#volunteer_role_id").val() == -1){
        cj(".volunteer_start-date").show();
        cj(".volunteer_shift-section").show();
      } else {
        cj(".volunteer_start-date").hide();
        cj(".volunteer_shift-section").show();
      }

      cj("#volunteer_role_id").change(function(){
        if(cj(this).val() == -1){
          cj(".volunteer_start-date").show();
          cj(".volunteer_shift-section").hide();
          cj(".other_role-section").show();
        } else {
          cj(".volunteer_start-date").hide();
          cj(".volunteer_shift-section").show();
          cj('#volunteer_need_id').empty();
          cj(".other_role-section").hide();
        }
      });
      cj('option:contains("Flexible")').text('Other Role');
      var shifts = cj('#volunteer_need_id option');
      cj('#volunteer_role_id').change(shifts, filterShifts);
      cj('#volunteer_role_id').trigger('change');
    </script>
    {/literal}
