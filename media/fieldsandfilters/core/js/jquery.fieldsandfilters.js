/**
 * @version     1.1.1
 * @package     com_fieldsandfilters
 * @copyright   Copyright (C) 2012 KES - Kulka Tomasz . All rights reserved.
 * @license     GNU General Public License version 3 or later; see License.txt
 * @author      KES - Kulka Tomasz <kes@kextensions.com> - http://www.kextensions.com
 */

;
(function ($, faf) {

	var $fn = $[faf] = {
        $name: faf,
        task: null
    };

	$.fn[faf] = function (type, options) {
		var $this = this;

		if (!$this.length) {
			return $this;
		}

		if (typeof type === 'object') {
			options = type;
			type = 'filters';
		}

		switch (type) {
			case 'pagination' :
				options = $.extend(true, {
					selector  : 'a',
					pagination: 'limitstart'
				}, options);

				$fn.pagination($this, options);
				break;
			case 'reset':
				$this.on('click', function () {
					$($fn.selector('form') + ':eq(0)').trigger('clear');
				});
				break;
			case 'submit':
				$($fn.selector('form') + ':eq(0)').trigger('submit');
				break;
            case 'random:all':
                // [TODO] move this to separate files in [types].js
                $this.on('click', function (e) {
                    e.preventDefault();

                    $($fn.selector('form') + ':eq(0)').trigger(type);
                });
                break;
            case 'random:selected':
                $this.on('click', function (e) {
                    e.preventDefault();

                    $($fn.selector('form') + ':eq(0)').trigger('random:selected');
                });
                break;
			case 'filters' :
			default:
				options = $.extend(true, {
					selectors: {
						body        : '#main',
						form        : '.faf-filters-form',
						input       : '.faf-filters-input',
						group       : '.faf-control-group',
						count       : '.faf-filters-count',
						reset       : '.faf-form-reset',
						fieldset    : '.faf-filters',
						empty       : '.faf-form-empty',
						submit      : '.faf-form-submit',
						other       : '',
						loadingClass: 'faf-filters-loading'
					},
					excluded : ['view'],
					setCount : true,
					hideCount: true
				}, options);

				// initialization
				$fn.init(options);

				// add modul id
				$fn.def('$request.modules', []).push($fn.get(options, 'module', null));

				// add fields id
				$fn.set('$request.fields', $.merge($fn.get('$request.fields', []), $fn.get(options, 'fields', [])));

				// delete not need options
				$fn.del(options, [ 'request', 'token', 'fields', 'selectors', 'fn' ]);

				// add options to global
				$fn.options($this, options);

				$fn.inputs($this);

				// $fn.setCounts( $this );

				// add event submint form
                $this.on({
                    submit: function (e) {
                        /* stop form from submitting normally */
                        e.preventDefault();

                        $fn.pagination('reset');

                        $fn.ajax($(this));
                    },
                    clear : function (e, not) {
                        e.preventDefault();

                        not = !(not === false || not === undefined);

                        if ($.isEmptyObject($fn.get('$data', {}))) {
                            return;
                        }

                        $fn.reset($(this));

                        if (!not) {
                            $($fn.selector('form')).not($(this)).triggerHandler('reset', not);

                            if ($fn.selector('other')) {
                                $fn.reset($($fn.selector('other'), $fn.selector('body')));
                            }

                            $(this).triggerHandler('submit');
                        }
                    },
                    'random:all': function (e) {
                        // [TODO] move this to separate files in [types].js
                        e.preventDefault();

                        $($fn.selector('input')).each(function() {
                            var val = Math.floor(Math.random() * 10) % 2;

                            $(this).prop($(this).is('input') ? 'checked' : 'selected', val);

                            if (val && $(this).is(':disabled')) {
                                $(this).prop('disabled', false);
                            }
                        });

                        $fn.task = e.type;

                        $(this).triggerHandler('submit');
                    },
                    'random:selected': function (e) {
                        $fn.task = e.type;

                        $(this).triggerHandler('submit');
                    }
                })
					.removeClass($fn.selector('loadingClass'))
					.find([$fn.selector('reset'), $fn.selector('empty')].join()).on('click', function (e) {
						e.preventDefault();
						$this.trigger('clear');
					})
					.filter($fn.selector('empty')).hide();

				break;
		}

		return $this;
	};

// core
	$.extend($fn, {
		loading: function (type) {
			type = type || 'start';
			switch (type) {
				case 'stop' :
					$(this.selector('body')).removeClass(this.selector('loadingClass'));
					break;
				case 'start' :
				default :
					$(this.selector('body')).addClass(this.selector('loadingClass'));
					break;
			}
		},

		init: function (options) {
			if (!this.get('$init')) {
				// selectors
				this.set('$selectors', this.get(options, 'selectors', {}));

				// forms ID
				var formsID = $(this.selector('form')).map(function () {
					if (!$(this).is('[id]')) {
						$(this).attr('id', (+new Date()).toString(36));
					}
					return $(this).attr('id');
				}).get();
				this.set('$forms', {
					first: formsID[0],
					last : formsID[formsID.length - 1],
					all  : formsID
				});
				// request && pagination
				this.set('$request', this.get(options, 'request', {}));
				this.set('$pagination', this.get(options, 'pagination', {}));
				this.set('$request', $.extend(this.get('$request', {}), this.get('$pagination', {})));

				// data temporalary for requerst
				this.set('$data', {});
				this.set('$url', this.get(options, 'url', '#'));

				// token
				this.token(this.get(options, 'token', null));

				// append function
				this.fn(this.get(options, 'fn', {}));

				// add event for other forms
				if (this.selector('other')) {
					$(this.selector('body')).on('submit', this.selector('other'), function (e) {
						e.preventDefault();
						$(this).fieldsandfilters('submit');
						return false;
					});
				}

				// excluded
				this.set('$excluded',
					$.merge($.map(this.get('$request', {}), function (v, key) {
						return key;
					}),
						this.get(options, 'excluded', [])
					));

				// is init
				this.set('$init', true);
			}
		},

		pagination: function (type, $pagination, options) {
			if (typeof type === 'object') {
				options = $pagination;
				$pagination = type;
				type = 'get';
			}

			switch (type) {
				case 'reset':
					this.set('$request', $.extend(this.get('$request', {}), this.get('$pagination', {})));
					break;
				case 'get':
				default:
					var url,
					    keys = this.get(options, 'pagination', []);

					keys = $.isArray(keys) ? keys : [keys];

					$pagination.on('click', this.get(options, 'selector', 'a'), function (event) {
						event.preventDefault();
						url = $(this).prop('search');
						$.map(keys, function (key) {
							$fn.set(( '$request.' + key ), $fn.getURLParameter(url, key, 0));
						});

						$fn.ajax();

						return false;
					});
					break;
			}

		},

		/**
		 * Get a fieldsandfilters value.
		 *
		 * string  path     Registry path (e.g. settings.globals)
		 * mixed   def          Optional default value, returned if the internal value is null.
		 *
		 * @return  mixed  Value of entry or null
		 *
		 * @since   1.0.0
		 */
		get: function (node, path, def) {
			var found = false, keys, key, i;

			if (typeof node !== 'object') {
				def = path;
				path = node;
				node = this;
			}

			def = def || null;

			if (!path) {
				return node;
			}
			else if (( keys = path.split('.') )) {
				for (i = 0, n = keys.length; key = keys[i], i < n; i++) {
					if (node.hasOwnProperty(key)) {
						node = node[key];
						found = true;
					}
					else {
						found = false;
						break;
					}
				}
			}
			if (found && node !== null && node !== '') {
				def = node;
			}

			return def;


		},

		/**
		 * Set a fieldsandfilters value.
		 *
		 * @param   string  path   fieldsandfilters Path (e.g. settings.globals)
		 * @param   mixed   value  Value of entry
		 *
		 * @return  mixed  The value of the that has been set.
		 *
		 * @since   1.0.0
		 */
		set: function (node, path, value) {
			var result = null, keys, key, i;

			if (typeof node !== 'object') {
				value = path;
				path = node;
				node = this;
			}

			if (( keys = path.split('.') )) {
				for (i = 0, n = keys.length - 1; key = keys[i], i < n; i++) {
					if (!node.hasOwnProperty(key) && ( i != n )) {
						node[key] = {};
					}
					node = node[key];
				}

				result = node[key] = value;
			}

			return result;
		},

		def: function (node, path, def) {
			if (typeof node !== 'object') {
				def = path;
				path = node;
				node = this;
			}

			return this.set(node, path, this.get(node, path, def));
		},

		del: function (node, paths) {
			var found = true, i;

			if (typeof node !== 'object') {
				paths = node;
				node = this;
			}

			if ($.isArray(paths)) {
				$.each(paths, function (i, path) {
					$fn.del(node, path);
				});
			}
			else if (( keys = paths.split('.') )) {
				for (i = 0, n = keys.length - 1; key = keys[i], i < n; i++) {
					if (!node.hasOwnProperty(key) && ( i != n )) {
						found = false;
						break;
					}

					node = node[key];
				}

				if (found) {
					delete node[key];
					return true;
				}
			}

			return false;
		},
        grep: function (data, callback, context) {
            for (var key in data) {
                if (callback.call(context || this, key, data[key])) {
                    return true;
                }
            }

            return false;
        }
	});

// request && options
	$.extend($fn, {
		requestID: function (createNew) {
			var newID = (+new Date()).toString(32).toUpperCase();

			return ( createNew ? this.set('$request.requestID', newID) : this.def('$request.requestID', newID) );
		},

		getURLParameter: function (url, name, def) {
			def = def || null;
			return decodeURIComponent(( new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(url) || [, ""])[1].replace(/\+/g, '%20')) || def;
		},

		token: function (newToken) {
			return ( newToken ? this.set('$token', newToken) : this.get('$token', null) );
		},

		options: function ($form, options) {
			var name = '$options.' + $form.attr('id');
			return ( typeof options === 'object' ? this.set(name, options) : this.get(name, {}) );
		},

		selector: function (name, def) {
			return $.trim(this.get(('$selectors.' + name), def || ''));
		},

		ajax: function ($form) {
			// new requerstID
			this.requestID(true);
			var data = {};

			if ($form) {
				var options = this.options($form),
					serialize = this.serialize();

				if ($.param(this.get('$data', {})) == $.param(serialize)) {
					return false;
				}

				$fn.set('$data', {});
				$.extend(true, this.get('$data', {}), serialize);
				this.set(data, 'module', $fn.get(options, 'module', null));
			}

			$.extend(true, data, this.get('$data', {}), this.get('$request', {}));
			this.set(data, $fn.token(), 1);

            if ($fn.task == 'random:selected')
            {
                var result = $fn.grep(data, function(name) {
                    return /^fieldsandfilters[\d+]*/.test(name);
                });

                if (!result) {
                    return false
                }

                this.set(data, 'random', 1);
            }

			// start loading data
			this.loading();
			$.getJSON(this.get('$url', '#'), data)
				.done(function (data, status, response) {
					if ($fn.requestID() != $fn.get(data, 'requestID')) {
						return false;
					}

                    if ($fn.get(data, 'empty', false) && $fn.task == 'random:all') {
                        $($fn.selector('form') + ':eq(0)').trigger('random:all');
                        return;
                    }

					if ($form) {
						// set new coutns
						$fn.set('$counts', $fn.get(data, 'counts', []));
						$fn.inputs($form, true);
						if ($fn.get(data, 'empty', false)) {
							$form.find($fn.selector('empty') + ':hidden').show().end()
								.find($fn.selector('submit') + ':visible').hide();
						}
						else {
							$form.find($fn.selector('empty') + ':visible').hide().end()
								.find($fn.selector('submit') + ':hidden').show();
						}

					}

					// set body
					$fn.body($fn.get(data, 'body', null)),

						// add styles and styles declaration
						$fn.styles($fn.get(data, 'head.styleSheets'), $fn.get(data, 'head.style'));

					// add scripts and scripts declaration
					$.when($fn.scripts($fn.get(data, 'head.scripts'), $fn.get(data, 'head.script'))).done(function () {
                        if ($fn.selector('other')) {
                            $($fn.selector('body')).find($fn.selector('other')).each(function(){
                                this.submit = function() {
                                    $(this).trigger('submit');
                                }
                            });
                        }

						// end loading data
						$fn.loading('stop');
						$fn.fn('done', [ $form, data, status, response ]);
					});

                    if ($fn.task == 'random:selected')
                    {
                        $fn.set('$data', {});
                    }

                    // clear task
                    $fn.task = null;
				})
				.fail(function (data, status, response) {
					// end loading data
					$fn.set('$data', {});
					$fn.loading('stop');
					$fn.fn('fail', [ $form, data, status, response ]);
				})
				.always(function (data, status, response) {
					var token;
					switch (status) {
						case 'success':
							token = $fn.get(data, 'token');
							break;
						case 'error':
						default:
                            // [TODO] jquery returned data.responseJSON
                            token = $fn.get($.parseJSON(data.responseText), 'token');
							break;
					}
					// add new token
					$fn.token(token);

					$fn.fn('always', [ $form, data, status, response ]);
				});
		}
	});

// html
	$.extend($fn, {
		styles: function (links, declarations) {
			if (typeof links === 'object' && !$.isEmptyObject(links)) {
				var styles = $('head link[href]').map(function () {
					return $(this).attr('href');
				});
				$.each(links, function (href, options) {

					if (typeof href === 'string' && $.inArray(href, styles) == -1) {
						$('<link/>').attr({
							href : href,
							rel  : 'stylesheet',
							type : options.mime,
							media: ( options.media ? options.media : "screen" )
						}).appendTo('head');
					}
				});
			}

			if (typeof declarations === 'object' && !$.isEmptyObject(declarations)) {
				$.each(declarations, function (type, declaration) {
					// when support end for ie 8 use:
					// $( '<style/>' ).attr( 'type', type ).text( declaration ).appendTo( 'head' );
					var style = document.createElement('style');
					style.type = type;
					if (style.styleSheet) {
						style.styleSheet.cssText = declaration;
					} else {
						style.appendChild(document.createTextNode(declaration));
					}

					$('head').append(style);
				});
			}
		},

		scripts: function (links, declarations) {

			var resources = [];
			if (typeof links === 'object' && !$.isEmptyObject(links)) {
				var scripts = $('script[src]').map(function () {
					return $(this).attr('src');
				});
				$.each(links, function (src, options) {
					if (typeof src === 'string' && $.inArray(src, scripts) == -1) {
						resources.push(src);
					}
				});
			}

			return $.when.apply(null, $.map(resources, $.getScript)).done(function () {
				if (typeof declarations === 'object' && !$.isEmptyObject(declarations)) {
					$.each(declarations, function (type, declaration) {
						// when support end for ie 8 use:
						// $( '<script/>' ).attr( 'type', type ).text( declaration ).appendTo( 'body' );
						var script = document.createElement('script');
						script.type = type;
						script.text = declaration;
						$('body').append(script);
					});
				}
			});
		},

		body: function (body) {
			if (body !== false) {
				$(this.selector('body')).html(body);
			}
		}
	});

// function
	$.extend($fn, {
		$fn    : {},
		$inputs: {},

		fn: function (name, callback, path) {
			var fn;
			path = path || '$fn.';

			if (typeof name === 'object') {
				path = callback || path;
				return $.map(name, function (callback, key) {
					return { key: ( $.isFunction(callback) ? $fn.set(( path + key ), callback) : false ) };
				});
			}
			else if ($.isFunction(callback)) {
				return $fn.set(( path + name ), callback);
			}
			else if (( fn = this.get(path + name) ) && $.isFunction(fn)) {
				return fn.apply($fn, ( $.isArray(callback) ? callback : [callback] ));
			}
			return false;
		},

		inputs: function ($form, forAll, parent) {
			var options, inputs, self,
				forms = $(forAll ? this.selector('form') : $form),
				fns = this.get('$inputs', {}),
				inputSel = this.selector('input');

			if (!$.isEmptyObject(fns) && inputSel) {
				forms.each(function () {
					self = $(this);
					options = $fn.options(self);
					inputs = self.find(inputSel);

					$.each(fns, function (key, fn) {
						if (!$.isFunction(fn)) {
							delete fns[key];
						}

						fn.apply(inputs, [ self, options, forAll, $fn ]);
					});
				});
			}

			return ( !parent && inputSel ? forms.find(inputSel) : forms );
		},

		hash: function (string) {
			var hash = 0, i;
			if (string.length == 0) {
				return hash;
			}
			for (i = 0; i < string.length; i++) {
				hash = ( ( hash << 5 ) - hash ) + string.charCodeAt(i);
				hash = hash & hash; // Convert to 32bit integer
			}
			return hash;
		},

		serialize: function ($form) {
			var obj = {}, prop, excluded = this.get('$excluded', []);
			$form = $($form || this.selector('form'));

			if (this.selector('other')) {
				$form = $form.add(this.selector('other'), this.selector('body'));
			}

            $.each($form.serializeArray(), function (k, el) {
                if ($.inArray(el.name, excluded) != -1) {
                    return;
                }

                if (( prop = $fn.get(obj, el.name) )) {
                    if (!$.isArray(prop)) {
                        prop = $fn.set(obj, el.name, [ prop ]);
                    }

                    if (el.value) {
                        prop.push(el.value);
                    }
                }
                else if (el.value) {
                    $fn.set(obj, el.name, el.value);
                }
            });

			return obj;
		},

		reset: function ($form) {
			$form
				.find('input:text, input:password, input:file, select, textarea')
				.val('');

			$form
				.find('input:radio, input:checkbox')
				.removeAttr('checked')
				.removeAttr('selected');

			return $form;
		},

		isSelectEnabled: function (option) {
			return Boolean($(option).parents('select').find(':enabled:not([data-default])').size());
		}
	});

// elements
	$fn.fn({
		dataCount: function (form, options, forAll) {
			var counts = ( forAll ? $fn.get('$counts', {}) : $fn.get(options, 'counts', {}) ), value;
			$(this).each(function () {
				$(this).data('count', counts.hasOwnProperty(value = $(this).val()) ? counts[value] : 0);
			});
		},

		setCount: function (form, options, forAll) {
			var group = $fn.selector('group'),
				spanSel = $fn.selector('count'),
				count;

			if (spanSel && spanSel) {
				$(this).each(function () {
					count = $(this).data('count');

					if ($(this).is('option')) {
						$(this).text($(this).text().replace(/(\()(?!.*\()\d+\)/g, '(' + count + ')'))
					} else {
						$(this).parents(group).find(spanSel).text(count !== false ? count : 0);
					}
				});
			}
		},

		hideCount: function (options, forAll) {
			var group = $fn.selector('group');
			if (group) {
				$(this).each(function () {
					if ($(this).data('default')) {
						return;
					}

					if (!$(this).data('count')) {
						// $( this ).attr( 'disabled', true );
						$(this).attr('disabled', true);

						if ($(this).is('option') && $fn.isSelectEnabled(this)) {
							return;
						}

                        $(this).parents(group).addClass('faf-hide').hide();
					}
					else {
						$(this).attr('disabled', false);
						if ($(this).is('option') && !$fn.isSelectEnabled(this)) {
							return;
						}

                        $(this).parents(group).removeClass('faf-hide').show();
					}
				}).parents($fn.selector('fieldset')).each(function () {
					var counts = $(this).find($fn.selector('input') + ':enabled:not([data-default])').size(),
					    visible = $(this).is(':visible');

					if (!visible && counts) {
						$(this).show();
					}
					else if (visible && !counts) {
						$(this).hide();
					}
				});
			}
		}

	}, '$inputs.');

})(jQuery, 'fieldsandfilters');