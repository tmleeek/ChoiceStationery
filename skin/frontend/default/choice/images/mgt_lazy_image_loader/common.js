jQuery(document).ready(function(jQuery) {
  jQuery('img.lazy').jail({
    event: 'load+scroll',
    placeholder : "/skin/frontend/default/choice/images/mgt_lazy_image_loader/loader.gif",
  });
});