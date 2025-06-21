


<?php
$input = "1,2,3,4,5,6,7";

$numbers = explode(',', $input);     // Split string by comma
$sum = array_sum($numbers);          // Sum the resulting array

echo "Sum: $sum";

?>

<script>
    let walk = {
        name:'Sham',
        run : function(speed){
            console.log(this.name + ' walk at ' + speed + ' mph.');

        }
    };
    let run = walk.run.bind(walk, 20);
    run();
</script>
