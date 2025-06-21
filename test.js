
    let walk = {
        name:'Sham',
        run : function(speed){
            console.log(this.name + ' walk at ' + speed + ' mph.');

        }
    };
    let run = walk.run.bind(walk, 20);
    run();

