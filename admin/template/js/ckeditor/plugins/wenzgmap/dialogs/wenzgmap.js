/**
 * @license MIT 
 *
 * Creato by Webz Ray
 */
CKEDITOR.dialog.add( 'wenzgmapDialog', function( editor ) {

    return {
        title: 'Вставьте карту Google',
        minWidth: 400,
        minHeight: 75,
        contents: [
            {
                id: 'tab-basic',
                label: 'Основные настройки',
                elements: [
                    {
                        type: 'text',
                        id: 'addressStr',
                        label: 'Пожалуйста, введите ваш адрес (страна, город, улица, номер дома)'
                    },
                    {
                        type: 'text',
                        id: 'mapWidth',
                        label: 'Ширина карты (px)',
                        style:'width:25%;',
                    },
                    {
                        type: 'text',
                        id: 'mapHeight',
                        label: 'Высота карты (px)',
                        style: 'width:25%;',
                    },
					{
                        type: 'text',
                        id: 'scale',
                        label: 'Масштаб (число 0-21)',
                        style: 'width:25%;',
                    }

                ]
            }
        ],
        onOk: function() {
            var dialog = this;
            var url = dialog.getValueOf('tab-basic', 'addressStr').trim();
            var mapWidth = dialog.getValueOf('tab-basic', 'mapWidth').trim();
            var mapHeight = dialog.getValueOf('tab-basic', 'mapHeight').trim();
			var scale = dialog.getValueOf('tab-basic', 'scale').trim();
			/*var regExURL=/v=([^&$]+)/i;
			var id_video=url.match(regExURL);
			
			if(id_video==null || id_video=='' || id_video[0]=='' || id_video[1]=='')
				{
				alert("URL invalid! Try a sample like a\n\n\t http://www.youtube.com/watch?v=abcdef \n\n Thank you!");
				return false;
				}
            */
			var content = '';
			
			content = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" width="' + mapWidth + '" height="' + mapHeight + '" src="//maps.google.com/maps?q=' + url + '&num=1&t=m&ie=UTF8&z=' + scale + '&output=embed" ';
			content += 'allowfullscreen></iframe></div>';
			var instance = this.getParentEditor();
						instance.insertHtml( content );
        }
		
    };
});