<div style="width: 100%; display:flex;background-color: #fff;padding: 10px;">
	<div style="height:150px;margin-right: auto;">
		<canvas style="height: 100%;" id="myChart"></canvas>
	</div>
</div>
<script>
$(function () {
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [
                "{{html_entity_decode("Bản tin")}}",
                "{{html_entity_decode("Tiếp sóng")}}",
                "{{html_entity_decode("Thu phát FM")}}",
                "{{html_entity_decode("Bản tin văn bản")}}"
            ],
            datasets: [{
                data: [
	                {{ $programs[0]->types }}, 
	                {{ $programs[1]->types }}, 
	                {{ $programs[2]->types }}, 
	                {{ $programs[3]->types }}
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                ],
                borderColor: [
                    'rgba(255,99,132,1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
        	
        }
    });
});
</script>