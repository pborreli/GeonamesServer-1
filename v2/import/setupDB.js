j = 0;

(function(){

    var addNamesIndex = function(place) {
		    
        if( ! place.alternatenames)
            {
                    place.alternatenames = '';
            }

            var names = [];

            if(typeof place.asciiname.toLowerCase == 'function') {
                    names.push(place.asciiname.toLowerCase());
            }

            if(typeof place.alternatenames.split !== 'function')
            {
                    if(typeof place.name.toLowerCase == 'function') {
                            names.push(place.name.toLowerCase());
                    }
            }
            else
            {
                    tmpalt = place.alternatenames.split(',');

                    for(i in tmpalt)
                    {
                            if(tmpalt[i] != '')
                            {
                                    names.push(tmpalt[i].toLowerCase());
                            }
                    }
            }

            place.names = names;

            if(place.longitude && place.latitude) {
		place.pin = new Array();
		place.pin.location = new Array();
		place.pin.location['lat'] = place.latitude;
		place.pin.location['lon'] = place.longitude;
            }

            db.countries.save(place);
    }


    var cur = db.countries.find();

    while(cur.hasNext())
	{
	    j++;
	    addNamesIndex(cur.next());
	}
    // db.countries.find().limit(100000000).forEach(addNamesIndex);
    db.countries.ensureIndex({ names : 1 });
    db.countries.ensureIndex({ loc : "2d" });
    db.countries.ensureIndex({ countryCode : 1 });
    print(j);
    
})();
