function get(data,callback){
	$.ajax({
		type:'GET',
		url:'../kyyla.php',
		data:data,
		dataType:'json',
		success:callback,
		error:function(e){
			console.log(e.responseText);
			callback(false);
		}
	});
}

function scale(arr){

	var total = 0,
		result = [];

	each(arr,function(){
		total += this;
	});

	each (arr,function(i){
		result[i] = this / total;
	});

	return result;
}

function scaleTo(height,arr){
	var max = 0;

	each(arr,function(){
		if (this>max){
			max = this;
		}
	});

	var ratio = height / max;
	var result = [];

	each(arr,function(i){
		result[i] = this / ratio;
	});

	return result;
}

function getScale(height,arr){
	var max = 0;

	each(arr,function(){
		if (this>max){
			max = this;
		}
	});

	var ratio = height / max;
	return ratio;
}
function addUser(username){
	$('#user-list').append('<div id="'+username+'" class="user"><h2>'+username+'</h2></div>');
}

function each(arr,fn){
	for (var i in arr){
		fn.call(arr[i],i);
	}
}

var lastColor = false;
function getColor(){
	var color = false;
	var colors = ['7DA649','CC1943','E68A50','13808C','248077','74AD8D','C82754','FCF9B9','F7BB21','F9E2B7'];

	var pos = parseInt(Math.random()*colors.length);
	if (pos>colors.length) pos = colors.length;

	if (colors[pos] == lastColor){
		color = getColor();
	} else {
		color = colors[pos];
	}
	lastColor = color;
	return color;
}

function getCategoryColor(cat){

    var colors = {

	    defaultColor:[91, 109, 207],
	    'kulttuuri':[245,105,108],
	    'urheilu':[198,222,100],
	    'talous':[20,185,214],
	    'etusivu':[247,187,33],
	    'teema':[198,222,100],
	    'uutiset':[69,109,189],
	    'mielipide':[89,196,188],
	    'artikkeli':[208, 212, 218],
	    'kotimaa':[255,222,75],
	    'ulkomaat':[94,109,129],
	    'viikonvaihde':[182, 76, 140],
	    'kulttuuri':[226, 101, 30],//[182, 76, 140],
	    'teema':[198,45,67],
	    kotimaa:[91, 109, 207],	
    }

    if (cat){
    	cat = cat.toLowerCase();
    } else {
    	cat = colors['defaultColor'];
    }

    if (colors[cat]){
    	return 'rgb('+colors[cat].join(',')+')';
    } else {
    	return 'rgb('+colors['defaultColor']+')';
    }
}

function makeBar(div,data){
	div = $(div);

	each(data,function(name){
		
		var item = $('<div class="item" data-name="'+name+'" data-percent="'+this*100+'"></div>');			
		
		item.css({
			width:this*100+'%',			
			'background-color':getCategoryColor(name)
		});

		item.append('<span>'+name+'</span>')

		div.append(item);
	});			

	div.append('<div class="detail-box"></div>');
	div.css({
		opacity:1
	})
}


function makeArticleBar(div,e){
	var articles = [];
	var total  = 0;

	each(e,function(){
		articles.push(this);

		each(this,function(){
			total += this;	
		});			
	});


	articles.sort(function(a,b){
		var suma = 0,
			sumb = 0;

		each(a,function(){
			suma += this;
		});

		each(b,function(){
			sumb += this;
		});

		return sumb-suma;
	});

	var category = '';
	each(articles,function(){
		each(this,function(id){

			each(e,function(cat){
				each(this,function(t_id){
					if (id == t_id){
						category = cat;
					}
				})
			});
			
			var w = this/total;
			var item = $('<div class="item article-item" category="'+category+'" article_id="'+id+'" data-percent="'+w*100+'"><span></span></div>');

			item.css({			
				'width':w*100+'%',
				'background-color':getCategoryColor(category),
			});			

			$(div).append(item);
		});
	});	

	$(div).css('opacity',1);
}

function makeCloud(e){
	each(e,function(key){
		$('#read-keywords').append('<span style="padding:'+20*this.value+'px; background-color:'+getCategoryColor(this.category)+';font-size:'+50*this.value+'px; data-value="'+this.value+'">'+this.text+'</span>')
	});

	$('#read-keywords').css({
		opacity:1
	});
}

function showUser(name){
	$('#scroller').addClass('left');
	$('.bar').empty().css({
		opacity:0
	});
	
	$('#read-keywords').empty().css({
		opacity:0
	});


	$('.tall').removeClass('tall');
	$('#username').text(name);
	

	/*
	get({user:name,type:'keyphrasecloud'},function(e){
		each(e,function(key){
			$('#read-keywords').append('<span style="padding:'+20*this.value+'px; background-color:'+getCategoryColor(this.category)+';font-size:'+50*this.value+'px; data-value="'+this.value+'">'+this.text+'</span>')
		});

		$('#read-keywords').css({
			opacity:1
		})

	})
	*/

	get({user:name,type:'statistics'},function(e){		
		var readCount = scale(e.categoryreads);
		makeBar('#category-readcount',readCount);
		makeBar('#category-readtime',e.readtime);			



		makeBar('#combined-recommended-categories',e.categories);
		makeBar('#recommended-categories',e.categoriesbyread);				


		makeCloud(e.keyphrases);
				
		makeArticleBar('#keyphrase-recommended-articles',e.keyphrasearticles);
		makeArticleBar('#recommended-articles',e.articles);
		


		$('#'+name).removeClass('busy');
	})
	/*

	get({user:name,type:'combinedcategories'},function(e){
		makeBar('#combined-recommended-categories',e);
	});

	
	get({user:name,type:'articlesbykeyphrase'},function(e){		
		makeArticleBar('#keyphrase-recommended-articles',e);
	})

	get({user:name},function(e){

		makeBar('#recommended-categories',e.categories);				
		makeArticleBar('#recommended-articles',e.articles);
*/

		/*
		each(e.articles,function(id){
			total += parseFloat(this);
			articles.push([id,parseFloat(this)]);
		});

		each(articles,function(i){
			articles[i][1] = articles[i][1] / total;
		})

		articles.sort(function(a,b){
			return b[1]- a[1];
		});

		each(articles,function(){
			var id = this[0];
			var w = this[1];
			var item = $('<div class="item" id="article_'+id+'" data-percent="'+w*100+'"><span></span></div>');
			
			item.css({			
				'width':w*100+'%'
			});

			$('#recommended-articles').append(item);

			get({'article':id},function(e){
					$('#article_'+id).find('span').text(e.title);
					$('#article_'+id).css({
						'background-color':getCategoryColor(e.category),
					});
				//item.prepend('<div class="category-mark" style="background-color:'+getCategoryColor(e.category)+'"></div>');

			});
		});
		*/
		//$('#'+name).removeClass('busy');
	//});

}

function showArticle(id){
	$('#scroller').addClass('on-right');

	get({article:id},function(e){
		console.log(e);
		$('p.article-text').html(e.text);
		$('h2.article-header').html(e.title).css({
			'background-color':getCategoryColor(e.category.toLowerCase())
		});
	})
}


function getimgList(callback){
	$.ajax({
		url:'img.php',
		type:'get',
		dataType:'json',
		data:{
			'list':'../../puru/images'
		},
		success:function(e){
			callback(e);
		}
	})
}


$(function(){
	var barHeight = 300;
/*

	getimgList(function(e){
		var count = 0;			

		each(e,function(i){
			count++;


			if (count<36){
				console.log(this);
				$('#wrapper').append(
					'<img src="img.php?img='+this+'&crop='+window.innerWidth/6+'&filter=grayscale" style="opacity:1">'
				);
			}

		});
	});
*/



	$('#scroller').on('click','#user-data',function(e){
		$('#scroller').removeClass('left');
	});

	$('#scroller').on('click','#article-container',function(e){
		$('#scroller').removeClass('on-right');
	});

	$('#user-list').on('click','.user',function(e){
		var el = $(this);
		if (!(el.hasClass('busy'))){
			el.addClass('busy');
			showUser(el.attr('id'));
		}
	});	

	$('#user-data').on('click','.article-item',function(e){
		e.stopPropagation();
		e.preventDefault();
		showArticle($(this).attr('article_id'));
	});

	$('#user-data').on('click','.bar',function(e){

		e.stopPropagation();
		$(this).toggleClass('tall');


		if ($(this).hasClass('tall')){
			var heights = [];
			var totalWidth = $(this).innerWidth();
			var items = $(this).find('.item');

			items.each(function(){
				heights.push(parseFloat($(this).attr('data-percent')));
			});

			var ratio = getScale(barHeight,heights);
			var count = $(this).find('.item').length;

			items.each(function(){
				$(this).css({
					height:ratio * $(this).attr('data-percent'),
					width:(totalWidth / count)
				});
			});
			
			items.last().css({
				width:(totalWidth / count) -1
			});

		} else {
			$(this).find('.item').each(function(){
				$(this).css({
					height:'100%',
					width: $(this).attr('data-percent') + '%'
				})
			});			
		}
		//$(this).parent().find('.detail-box').toggleClass('visible');
	});

	get({users:1},function(e){		
		if (e && e.ok != false){
			each(e,function(){
					if (this != ''){					
						addUser(this);
						var user = this;
						get({user:this,type:'activity'},function(e){
							if (typeof(e)!='object'){
								$('#'+user).append('<span>'+e+'</span>')
							}
						});
					}
			});
		}
	})

});