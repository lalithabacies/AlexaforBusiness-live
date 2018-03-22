function qsmChartInit() {

  // Cycle through each of the questions
  for (var i = 0; i < qsmAnswerData.question.length; i++) {

    // Add in line breaks if the string is longer than 50 characters
    if ( qsmAnswerData.question[i].questionText.length > 50 ) {
      var tempTextArray = qsmAnswerData.question[i].questionText.match(/(.{1,50}(?=\s|$))/g);
      var tempText = '';
      for ( var j = 0; j < tempTextArray.length; j++ ) {
        tempText += tempTextArray[j] + '<br>';
      }
      qsmAnswerData.question[i].questionText = tempText;
    }

    // Create a new div for each question
    jQuery( ".qsm_answer_data_section" ).append( '<div class="qsm_stats_container qsm_stats_answers question_' + i + '">' +
      '<div class="qsm_chart_selectors"><button class="qsm_pie_chart_selector button btn ink-reaction btn-raised btn-sm btn-primary">Pie Chart</button>' +
      '<button class="qsm_bar_chart_selector button btn ink-reaction btn-raised btn-sm btn-primary">Bar Chart</button>' +
      '<button class="qsm_word_chart_selector button btn ink-reaction btn-raised btn-sm btn-primary">Word Count Chart</button>' +
      '<button class="qsm_box_chart_selector button btn ink-reaction btn-raised btn-sm btn-primary">Box Chart</button>' +
      '<button class="qsm_multi_chart_selector button btn ink-reaction btn-raised btn-sm btn-primary">Multiple Response</button>' +
      '<button class="qsm_score_selector button btn ink-reaction btn-raised btn-sm btn-primary">Question Score</button>' +
      '<button class="qsm_individual_selector button btn ink-reaction btn-raised btn-sm btn-primary">Individual</button>' +
      '</div>' +
      '<div class="qsm_answer_canvas" id="chart_' + i + '"></div>' +
      '</div>'
    );

    // Create the initial chart for the question
    createPieChart( qsmAnswerData.question[i].questionText, qsmAnswerData.question[i].answers, qsmAnswerData.question[i].totalAnswers, 'chart_' + i, 'answer_legend_' + i );
  }
}

function qsmGetRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function createPieChart( question, answers, totalAnswers, chartID, legendID ) {
  var values = [];
  var labels = [];
  for ( var i = 0; i < answers.length; i++ ) {
    values.push( answers[i].totalSelected );
    labels.push( answers[i].answerText );
  }
  var graph_data = [
		{
			values: values,
			labels: labels,
			type: 'pie'
		}
	];
	var layout = {
    title: question,
    width: jQuery( '#' + chartID ).parent().width(),
    legend: {x: 0,y: -0.9},
    margin: {l:0, t: 50, r: 0, b: 0, pad: 50}
  };
	Plotly.newPlot( chartID, graph_data, layout, {displaylogo: false, showLink: false, sendData: false, modeBarButtonsToRemove: ['sendDataToCloud']}  );
}

function createMultiResponsePieChart( question, answers, totalAnswers, chartID, legendID ) {
  var values = [];
  var labels = [];
  for ( var i = 0; i < answers.length; i++ ) {
    var single_answers = answers[i].answerText.split( '.' );
    for (var j = 0; j < single_answers.length; j++) {
      var label_index = labels.indexOf( single_answers[j] );
      if ( label_index !== -1 ) {
        values[label_index] += answers[i].totalSelected;
      } else {
        values.push( 1 );
        labels.push( single_answers[j] );
      }
    }
  }
  var graph_data = [
		{
			values: values,
			labels: labels,
			type: 'pie'
		}
	];
  var layout = {
    title: question,
    width: jQuery( '#' + chartID ).parent().width(),
    legend: {x: 0,y: -0.9},
    margin: {l:0, t: 50, r: 0, b: 0, pad: 50}
  };
	Plotly.newPlot( chartID, graph_data, layout, {displaylogo: false, showLink: false, sendData: false, modeBarButtonsToRemove: ['sendDataToCloud']}  );
}

function createBarChart( question, answers, totalAnswers, chartID, legendID ) {
  var xValues = [];
  var yValues = [];
  for ( var i = 0; i < answers.length; i++ ) {
    xValues.push( answers[i].answerText );
    yValues.push( answers[i].totalSelected );
  }
  var graph_data = [
		{
			x: xValues,
			y: yValues,
			type: 'bar'
		}
	];
  var layout = {
    title: question,
    width: jQuery( '#' + chartID ).parent().width()
  };
	Plotly.newPlot( chartID, graph_data, layout, {displaylogo: false, showLink: false, sendData: false, modeBarButtonsToRemove: ['sendDataToCloud']}  );
}

function createWordCountChart( question, answers, totalAnswers, chartID, legendID ) {
  var combinedString = '';
  var commonWords = [
    'this', 'is', 'a', 'the', 'do', "don't", 'an', 'for', 'to', 'i', 'and', 'of', 'on',
    'that', 'my', 'it', 'in', 'was', 'were', 'we', 'our', 'gt', 'lt', 'br', 'h1', 'h2', 'h3',
    'h4', 'h5', 'h6', 'can', 'have', 'from', 'no', 'are', 'be', 'with', 'if', 'but', 'not', 'at', 'they',
    'them', 'as', 'then', 'than', 'so', 'also', 't', 'had', 'am', 's', 'has', 'or', 'nor', 'd', 're' ];
  for ( var i = 0; i < answers.length; i++ ) {
    combinedString += answers[i].answerText + ' ';
  }
  var wordCounts = {};
  var words = combinedString.split(/\b/);
  for( i = 0; i < words.length; i++ ) {
    words[i] = words[i].replace( /[.,-\/#!$%\^&\*;:{}=\-_`~()]/g, '' ).toLowerCase();
    if ( words[i].match(/[a-z]/i) && commonWords.indexOf( words[i] ) === -1 ) {
      wordCounts["_" + words[i]] = (wordCounts["_" + words[i]] || 0) + 1;
    }
  }
  keysSorted = Object.keys(wordCounts).sort(function(a,b){return wordCounts[a]-wordCounts[b];}).reverse().slice(0,20);
  answers = [];
  for (i = 0; i < keysSorted.length; i++) {
    answerValues = {
      totalSelected: wordCounts[keysSorted[i]],
      answerText: keysSorted[i].replace( '_', '')
    };
    answers.push( answerValues );
  }
  createBarChart( question, answers, totalAnswers, chartID, legendID );
}

function createBoxChart( question, answers, totalAnswers, chartID, legendID ) {
  var xValues = [];
  for ( var i = 0; i < answers.length; i++ ) {
    for (var j = 0; j < answers[i].totalSelected; j++) {
      xValues.push( answers[i].answerText );
    }
  }
  var graph_data = [
    {
      x: xValues,
      type: 'box',
      name: ''
    }
  ];

  var layout = {
    title: question,
    width: jQuery( '#' + chartID ).parent().width()
  };
	Plotly.newPlot( chartID, graph_data, layout, {displaylogo: false, showLink: false, sendData: false, modeBarButtonsToRemove: ['sendDataToCloud']}  );
}

function createScoreDisplay( questionID ) {
  if ( 0 === qsmAnswerData.system ) {
    jQuery( '#chart_' + questionID ).html( "<div class='qsm_score_canvas_title'> " + qsmAnswerData.question[questionID].questionText + "</div><div class='qsm_score_canvas_content'>" + ( qsmAnswerData.question[questionID].correct * 100 ) + '%' + "</div>" );
  } else {
    jQuery( '#chart_' + questionID ).html( "<div class='qsm_score_canvas_title'> " + qsmAnswerData.question[questionID].questionText + "</div><div class='qsm_score_canvas_content'>" + qsmAnswerData.question[questionID].averagePoints + "</div>" );
  }
}

function createIndividualDisplay( questionID ) {
  var answerHTML = '';
  for ( var i = 0; i < qsmAnswerData.question[questionID].answers.length; i++ ) {
    for (var j = 0; j < qsmAnswerData.question[questionID].answers[i].totalSelected; j++) {
      answerHTML += "<p>" + qsmAnswerData.question[questionID].answers[i].answerText + "</p><hr>";
    }
  }
  jQuery( '#chart_' + questionID ).html( "<div class='qsm_individual_canvas_title'> " + qsmAnswerData.question[questionID].questionText + "</div><div class='qsm_individual_canvas_content'>" + answerHTML + "</div>" );
}

function switchToChart( type, questionID ) {
  jQuery( '#chart_' + questionID ).remove();
  jQuery( '#answer_legend_' + questionID ).remove();
  jQuery( '.question_' + questionID ).append('<div class="qsm_answer_canvas" id="chart_' + questionID + '" ></div>');
  switch ( type ) {
    case 'pie':
      createPieChart( qsmAnswerData.question[questionID].questionText, qsmAnswerData.question[questionID].answers, qsmAnswerData.question[questionID].totalAnswers, 'chart_' + questionID, 'answer_legend_' + questionID );
      break;
    case 'bar':
      createBarChart( qsmAnswerData.question[questionID].questionText, qsmAnswerData.question[questionID].answers, qsmAnswerData.question[questionID].totalAnswers, 'chart_' + questionID, 'answer_legend_' + questionID );
      break;
    case 'word':
      createWordCountChart( qsmAnswerData.question[questionID].questionText, qsmAnswerData.question[questionID].answers, qsmAnswerData.question[questionID].totalAnswers, 'chart_' + questionID, 'answer_legend_' + questionID );
      break;
    case 'box':
      createBoxChart( qsmAnswerData.question[questionID].questionText, qsmAnswerData.question[questionID].answers, qsmAnswerData.question[questionID].totalAnswers, 'chart_' + questionID, 'answer_legend_' + questionID );
      break;
    case 'multiresponse' :
      createMultiResponsePieChart( qsmAnswerData.question[questionID].questionText, qsmAnswerData.question[questionID].answers, qsmAnswerData.question[questionID].totalAnswers, 'chart_' + questionID, 'answer_legend_' + questionID );
      break;
    case 'score':
      createScoreDisplay( questionID );
      break;
    case 'individual':
      createIndividualDisplay( questionID );
      break;
    default:

  }
}

function downloadExport( exportFile ) {
  window.open( exportFile.filename );
}

qsmChartInit();

jQuery( '.qsm_pie_chart_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'pie', canvasIDArray[1]);
});

jQuery( '.qsm_bar_chart_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'bar', canvasIDArray[1]);
});

jQuery( '.qsm_word_chart_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'word', canvasIDArray[1]);
});

jQuery( '.qsm_box_chart_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'box', canvasIDArray[1]);
});

jQuery( '.qsm_multi_chart_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'multiresponse', canvasIDArray[1]);
});
jQuery( '.qsm_score_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'score', canvasIDArray[1]);
});
jQuery( '.qsm_individual_selector' ).on( 'click', function( event ) {
  var canvasID = jQuery( this ).parent().siblings( '.qsm_answer_canvas' ).attr( 'id' );
  var canvasIDArray = canvasID.split('_');
  switchToChart( 'individual', canvasIDArray[1]);
});

jQuery( '.qsm_export_results_button' ).on( 'click', function( event ) {
  var data = {
		action: 'qsm_reporting_export',
		quizID: filterData.quizID,
    user: filterData.user,
    name: filterData.name,
    business: filterData.business,
    start_date: filterData.start_date,
    end_date: filterData.end_date
	};

	jQuery.post( ajaxurl, data, function( response ) {
		downloadExport( JSON.parse( response ) );
	});
});
