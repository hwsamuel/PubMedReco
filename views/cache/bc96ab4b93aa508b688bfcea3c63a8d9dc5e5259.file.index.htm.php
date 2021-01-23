<?php /* Smarty version Smarty-3.1.8, created on 2021-01-23 06:53:50
         compiled from "./views\index.htm" */ ?>
<?php /*%%SmartyHeaderCode:1487555908600bb906d22ba5-20995450%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bc96ab4b93aa508b688bfcea3c63a8d9dc5e5259' => 
    array (
      0 => './views\\index.htm',
      1 => 1611381225,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1487555908600bb906d22ba5-20995450',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.8',
  'unifunc' => 'content_600bb906d5b4d5_33895516',
  'variables' => 
  array (
    '_smartyUsers' => 0,
    'user' => 0,
    '_smartyProfile' => 0,
    '_smartySettings' => 0,
    '_smartyThreads' => 0,
    '_smartyDataFile' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_600bb906d5b4d5_33895516')) {function content_600bb906d5b4d5_33895516($_smarty_tpl) {?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>PubMedReco: PubMed Citations Recommender</title>

	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="assets/css/style.css">	

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
    	<script src="assets/js/html5shiv.min.js"></script>
    	<script src="assets/js/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<div class="container">
		<div class="row" id="main-holder">
            <div class="col-md-8">
                <h3>PubMedReco: PubMed Citations Recommender</h3>
		  		<noscript>
		  	    <p class="alert alert-danger" role="alert">
		  	        This website will <b>not</b> function without JavaScript. Please enable JavaScript to continue.
		  	    </p>
		  	    </noscript>
		  	    
		 		<p id="read-holder">
		  			<span>Press <span class="label label-warning">SPACEBAR</span> to load next thread in selected dataset</span> 
		  			<span class="pull-right">
		  				<a href="#" class="label label-danger" id="autorun">Autorun</a>
		  				<a href="#" class="label label-success" data-toggle="modal" data-target="#scoresArea">Scores</a>
		  				<a href="#" class="label label-default" data-toggle="modal" data-target="#settingsArea">Settings <span class="glyphicon glyphicon-cog"</span></a>
					</span>
		  		</p>
		
                <form class="form-inline">
                	<div class="col-md3-12" id="chat-wrap">
                        <div id="chat-area"></div>
                    </div>
                </form>
            </div>
            <div class="col-md-4" id="right-area">
                <div class="panel panel-default">
                	<div class="panel-heading">Extracted Keywords Index</div>
					<div class="grid-wrapper panel-body small" id="keywords-area">
						<table cellpadding="2" cellspacing="2" width="100%">
							<thead>
								<tr>
									<th width="35%">Keyword</th>
									<th width="12%">Pos.</th>
									<th width="12%">Freq.</th>
									<th width="20%">Weight</th>
									<th width="10%"><span class='glyphicon glyphicon-thumbs-up'></span></th>
									<th width="11%"><span class='glyphicon glyphicon-thumbs-down'></span></th>
								</tr>
							</thead>
							<tbody id="keywords-index"></tbody>
						</table>
					</div>
				</div>
            </div>
        </div>
        
        <div class="row">
        	<div class="col-md-12">
				<div class="panel panel-default" id="reco-holder">
	               	<div class="panel-heading">
		               	Related PubMed Citations 
		               	<span class="small label label-info">TITLE</span> 
		               	<span class="small label label-primary">ABSTRACT</span>
		               	<span class="small label label-default label-light-gray">SERENDIPITY</span>
		               	<span class="small pull-right text-muted" id="runtime"></span>
		               	<span class="small">
			               	User View 
			               	<select name="userView" id="userView">
			               		<option value="">ALL USERS</option>
								<?php  $_smarty_tpl->tpl_vars['user'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['user']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['_smartyUsers']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['user']->key => $_smarty_tpl->tpl_vars['user']->value){
$_smarty_tpl->tpl_vars['user']->_loop = true;
?>
									<option value="<?php echo $_smarty_tpl->tpl_vars['user']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['user']->value;?>
</option>
								<?php } ?>
							</select>
						</span>
	               	</div>
	               	<div class="panel-body grid-wrapper small" id="recommend"></div>
				</div>
 			</div>
        </div>
        
        <div class="row">
        	<div class="col-md-12">
        		<p class="small">
        			<span class="pull-right logos"><img src="assets/img/mw-logo.png" /></span> 
        			&copy; 2015 Hamman Samuel. All Rights Reserved. This software is for research purposes only.
        			<br />
        			<span class="text-muted">Software and libraries used: </span> 
        			<a target="_blank" href="https://github.com/asifr/PHP-PubMed-API-Wrapper">PHP PubMed API Wrapper</a> |
        			<a target="_blank" href="http://www.dictionaryapi.com">Merriam-Webster's Medical Dictionary API</a> |
        			<a target="_blank" href="http://wordnet.princeton.edu">WordNet</a> |
        			<a target="_blank" href="http://stop-words.googlecode.com/svn/trunk/stop-words">Stop Words</a>
        			<br />
        			<span class="text-muted">Datasets from: </span>
        			<a target="_blank" href="http://www.optimalhealthnetwork.com/Alternative-Health-Live-Chat-Log-Archive-s/196.htm">Optimal Health Network</a> |
        			<a target="_blank" href="http://www.healthpost.ca/forums">Health Post</a> 
	        		</p>
        		</div>
        	</div>
        </div>
        
        <div class="modal" id="scoresArea" tabindex="-1" role="dialog" aria-labelledby="scoresAreaLabel" aria-hidden="true">
  			<div class="modal-dialog modal-sm">
    			<div class="modal-content">
      				<div class="modal-body">
	                	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4>Chat Profile</h4>
				    	<div class="small">
					    	Total threads <b><?php echo $_smarty_tpl->tpl_vars['_smartyProfile']->value[0];?>
</b><br />
					    	Threads with keywords <b><?php echo $_smarty_tpl->tpl_vars['_smartyProfile']->value[1];?>
</b><br />
					    	Total keywords <b><?php echo $_smarty_tpl->tpl_vars['_smartyProfile']->value[2]+$_smarty_tpl->tpl_vars['_smartyProfile']->value[3];?>
</b><br />
					    	Unique keywords <b><?php echo $_smarty_tpl->tpl_vars['_smartyProfile']->value[2];?>
</b><br />
							Repeated keywords <b><?php echo $_smarty_tpl->tpl_vars['_smartyProfile']->value[3];?>
</b><br />
							Average weight <b><?php echo number_format($_smarty_tpl->tpl_vars['_smartyProfile']->value[4],2);?>
</b><br />
						</div>
						
						<h4>Citation Impressions</h4>
						<div class="small">
							Total impressions <b><span id='totalrecs'>0</span></b><br />
							Unique impressions <b><span id='uniquerecs'>0</span></b><br />
							Repeated impressions <b><span id='repeatrecs'>0</span></b><br />
						</div>

						<h4>Score statistics</h4>
				    	<div class="grid-wrapper small scores-display">
							<table cellpadding="2" cellspacing="2" width="100%">
								<thead>
									<tr id="scores_header">
										<th width="50%">Pos.</th>
										<th width="50%">Avg.</th>
									</tr>
								</thead>
								<tbody id="scores_add"></tbody>
							</table>
						</div>
        			</div>
       			</div>
     		</div>
     	</div>
	    
        <div class="modal" id="settingsArea" tabindex="-1" role="dialog" aria-labelledby="settingsAreaLabel" aria-hidden="true">
  			<div class="modal-dialog modal-sm">
    			<div class="modal-content">
      				<div class="modal-body">
	        			<p><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></p>
	        			<h4>Settings</h4>
	        			<form method="post" action="index.php" role="form" class="small">
	                        <div class="form-group">
	                        	<label for="dataset">Source</label>
                        		<select class="form-control" name="dataset" id="dataset">
                                    <option value="1" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['dataset']==1)){?>selected="selected"<?php }?>>OptimalHealthNetwork.com</option>
                                    <option value="2" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['dataset']==2)){?>selected="selected"<?php }?>>HealthPost.ca</option>
                                </select>
	                        </div>
	                        <div class="form-group">
	                            <label for="subset">Dataset</label>
                            	<select class="form-control" name="subset" id="subset">
                                    <option value="1">Keep Current</option>
                                    <option value="2">Choose Random</option>
                                </select>
	                        </div>
	                        <div class="form-group">
	                            <label for="med_words">Med. Words Match</label>
                                <select class="form-control" name="med_words" id="med_words">
                                    <option value="1" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['med_words']==1)){?>selected="selected"<?php }?>>Exact matches</option>
                                    <option value="2" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['med_words']==2)){?>selected="selected"<?php }?>>Approx. matches</option>
                                </select>
                            </div>
	                        <div class="form-group">
	                            <label for="method">Method</label>
                                <select class="form-control" name="method" id="method">
                                    <option value="1" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['method']==1)){?>selected="selected"<?php }?>>Keyword Frequency</option>
                                    <option value="2" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['method']==2)){?>selected="selected"<?php }?>>Time Window</option>
                                    <option value="3" <?php if (($_smarty_tpl->tpl_vars['_smartySettings']->value['method']==3)){?>selected="selected"<?php }?>>Weighted Gradual Forgetting</option>
                                </select>
                            </div>
	                        <div class="form-group">
	                            <label for="threshValue"><span id="threshLabel"></span></label>
                               	<p class="label label-danger" id="errmsg"></p>
                               	<input class="form-control" type="text" name="threshValue" id="threshValue" value="<?php if (isset($_smarty_tpl->tpl_vars['_smartySettings']->value['threshValue'])){?><?php echo $_smarty_tpl->tpl_vars['_smartySettings']->value['threshValue'];?>
<?php }?>" /> 
	                        </div>
	                        <div class="form-group">
                        		<input type="submit" class="btn btn-primary" name="save_settings" value="Apply Settings" />
                        		<input type="submit" class="btn btn-default" data-dismiss="modal" value="Close" />
	                        </div>
		                </form>
      				</div>
    			</div>
  			</div>
		</div>
	</div>

	<script type="text/javascript">
		var _smartyThreads = <?php echo $_smarty_tpl->tpl_vars['_smartyThreads']->value;?>
;
		console.log('<?php echo $_smarty_tpl->tpl_vars['_smartyDataFile']->value;?>
');
	</script>

	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/ajax.js"></script>
	<script type="text/javascript" src="assets/js/indexer.js"></script>
	<script type="text/javascript" src="assets/js/chatsim.js"></script>
	<script type="text/javascript" src="assets/js/main.js"></script>
</body>
</html>
<?php }} ?>