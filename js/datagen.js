/* Copyright 2015 Zachary Doll */
jQuery(document).ready(function($) {
  // hide save button since we don't need it if JS is enabled
  $('form .Buttons').hide();
  
  // show text inputs
  $('.DataGenButtons input').show();
  
  // immediately submit language changes
  $('form select').change(function() {
    $(this).parents('form').submit();
  });
  
  // change anchors when data input changes
  $('.DataGenButtons input[name="CountUsers"]').change(function() {
    $('.DataGenButtons a.Users').attr("href", gdn.url('plugin/datagenerator/users/' + $(this).val()));
  });
  $('.DataGenButtons input[name="CountDiscussions"]').change(function() {
    $('.DataGenButtons a.Discussions').attr("href", gdn.url('plugin/datagenerator/discussions/' + $(this).val()));
  });
  $('.DataGenButtons input[name="CountComments"]').change(function() {
    $('.DataGenButtons a.Comments').attr("href", gdn.url('plugin/datagenerator/comments/' + $(this).val()));
  });
});
