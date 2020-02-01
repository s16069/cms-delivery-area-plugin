(function() {
  "use strict";

  const zoom = 11;

  const mapId = 'google-map';
	  
  const areas = [
    {
      inputId: 'woocommerce_area_rate_zone_1_distance',
      color: '#00FF00',
    },
    {
      inputId: 'woocommerce_area_rate_zone_2_distance',
      color: '#0000FF',
    },
    {
      inputId: 'woocommerce_area_rate_zone_3_distance',
      color: '#FF0000',
    }
  ];
	  
	function initAreas() {
		for (const area of areas) {
			const element = document.getElementById(area.inputId);
			area.input = element;
			area.radius = parseRadius(element.value);
			element.addEventListener('input', function() {
				area.radius = parseRadius(this.value);
				checkConstraints(areas);
			});
    }
    
		checkConstraints(areas);
	}
	  
	function parseRadius(value) {
		const radius = parseInt(value);
		return isNaN(radius) ? 0 : radius;
	}
	  
	function checkConstraints(areas) {
		for (let i=0; i<areas.length - 1; i++) {
			if (areas[i].radius > areas[i+1].radius) {
				areas[i+1].radius = areas[i].radius + 500;
			}
		}
		
		updateView(areas);
	}
	  
	function updateView(areas) {
		for (const area of areas) {
			area.input.value = '' + area.radius;
			if (area.circle && area.radius !== area.circle.getRadius()) {
				area.circle.setRadius(area.radius);
			}
		}
	}

	function initMap(center) {
		const map = new google.maps.Map(document.getElementById(mapId), {
			zoom: zoom,
			center: center,
			mapTypeId: 'terrain'
		});

		for(let i=areas.length - 1; i>=0; i--) {
			const area = areas[i];
			const circle = new google.maps.Circle({
				strokeColor: area.color,
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: area.color,
				fillOpacity: 0.2,
				map: map,
				center: center,
				radius: area.radius,
				editable: true,
				draggable: false,
			});
			
			google.maps.event.addListener(circle, 'radius_changed', function() {
				area.radius = Math.floor(circle.getRadius());
				checkConstraints(areas);
			});
			
			const circleCenter = circle.getCenter();
			google.maps.event.addListener(circle, 'center_changed', function() {
				if (!circleCenter.equals(circle.getCenter())) {
					circle.setCenter(circleCenter);
					map.setCenter(center);
				}
			});
			
			area.circle = circle;
		}
  }
  
  window.initAreas = initAreas;
  window.initMap = initMap;

})();