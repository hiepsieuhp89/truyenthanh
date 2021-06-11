<link href="https://vjs.zencdn.net/7.11.4/video-js.css" rel="stylesheet" />
<style>
  .videos-js{
    display: flex;flex-wrap: wrap;width:100%;height: 100%;
  }
  .videos-js .video{
    width:100%;height: 100%;
  }
  .video-js{
    width:80%;
  }
</style>
<script src="https://vjs.zencdn.net/7.11.4/video.min.js"></script>
<div class="container-fluid">
  <div class="row d-flex videos-js">
    <div class="video" style="margin: 1em;">
      <video id="my-video" class="video-js" controls preload="auto" data-setup="{}" >
        <source src="{{ $url }}" type="application/x-mpegURL" res="9999" label="auto" />
        <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
      </video>
    </div>
  </div>
</div>
<script>
  $(document).on('ready pjax:end',function(){
    let h = $('#my-video').width() * 9/16;
    $('#my-video').height(h);
  });
</script>
