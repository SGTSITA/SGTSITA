! function() {
	"use strict";
	var e = {
			572: function(e, t, n) {
				n.r(t), t.default = '<div class="">\r\n\r\n    <div class="dpw-header">\r\n        <div class="dpw-header-inner">\r\n            <div class="dpw-header-item dpw-header-main"><a href="https://javascript.daypilot.org/">DayPilot Pro for JavaScript</a></div>\r\n            <div class="dpw-header-right">\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n    <div class="dpw-subheader">\r\n        <div class="dpw-subheader-inner toolbar">\r\n        </div>\r\n    </div>\r\n\r\n    <div class="dpw-title">\r\n        <div class="dpw-title-inner">\r\n            <div class="download" style="display: flex;">\r\n                <div style="margin-right: 10px;"><a href="https://javascript.daypilot.org/files/daypilot-pro-javascript-trial-2022.3.5384.zip" class="inline-btn track-download"><span>Download</span></a></div>\r\n                <div style="flex-grow: 1;">\r\n                    <div>Download a trial version (1.1 MB).</div>\r\n                    <div><a href="https://javascript.daypilot.org/files/daypilot-pro-javascript-trial-2022.3.5384.zip" class="track-download">daypilot-pro-javascript-trial-2022.3.5384.zip</a></div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n    <div class="dpw-main">\r\n        <div class="dpw-sidebar menu">\r\n            <div class="search">\r\n                <div class="search-box"><input type="text" id="search-box-input" placeholder="Quick search"><button id="search-box-clear">&times;</button></div>\r\n            </div>\r\n\r\n        </div>\r\n        <div class="dpw-body">\r\n            <div class="dpw-body-inner">\r\n                <div class="placeholder"></div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <div class="dpw-footer">\r\n        <div class="dpw-footer-inner">\r\n        </div>\r\n    </div>\r\n\r\n</div>\r\n'
			},
			752: function(e, t, n) {
				(new(n(447).t)).init()
			},
			722: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.l = void 0;
				var n = function() {
					function e() {}
					return e.m = navigator && navigator.userAgent && (-1 !== navigator.userAgent.indexOf("MSIE") || -1 !== navigator.userAgent.indexOf("Trident")), e
				}();
				t.l = n
			},
			143: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.o = void 0;
				var n = function() {
					function e() {}
					return e.get = function(e) {
						return document.getElementById(e)
					}, e.query = function(e) {
						return Array.apply(null, document.querySelectorAll(e))
					}, e.h = function(e, t) {
						return e.getElementsByClassName(t)[0]
					}, e.create = function(e) {
						return document.createElement(e)
					}, e.u = function() {
						return document.createDocumentFragment()
					}, e
				}();
				t.o = n
			},
			730: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.v = void 0;
				var n = function() {
					function e() {}
					return e.prototype.g = function() {
						var e = this;
						document.addEventListener("DOMContentLoaded", (function() {
							Array.apply(null, document.querySelectorAll(".track-download")).forEach((function(t) {
								t.addEventListener("click", (function(n) {
									e.track(t, "/action/trialdownload") || n.preventDefault()
								}))
							}))
						}))
					}, e.prototype.download = function(e) {
						var t = document.createElement("a");
						t.href = e, t.download = e.split("/").pop(), document.body.appendChild(t), t.click(), document.body.removeChild(t)
					}, e.prototype.track = function(e, t) {
						var n = this,
							i = window.ga;
						return void 0 !== i && i("send", "pageview", t), "_blank" === e.target || (setTimeout((function() {
							n.download(e.href)
						}), 150), !1)
					}, e
				}();
				t.v = n
			},
			447: function(e, t, n) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.t = void 0;
				var i = n(572),
					l = n(4),
					a = n(143),
					r = n(11),
					m = n(463),
					o = n(730),
					s = n(151),
					h = n(722),
					c = function() {
						function e() {
							this.content = a.o.get("content"), this.location = new r.p(window.location.protocol, window.location.host, this.content.dataset.path || window.location.pathname), this.search = new m.C, this.S = new l.T, this.M = new o.v, this.version = ""
						}
						return e.prototype.init = function() {
							this.k()
						}, e.prototype.k = function() {
							this.version = this.content.dataset.version;
							var e = a.o.create("div");
							if (e.innerHTML = i.default, this.location.A) document.body.className = "dpw-no-sidebar";
							else {
								var t = a.o.h(e, "menu"),
									n = this.j();
								t.appendChild(n), document.body.className = "dpw-sidebar-left"
							}
							var l = a.o.h(e, "toolbar"),
								r = this.R();
							l.appendChild(r);
							var m = a.o.h(e, "placeholder");
							this.placeholder = m, h.l.m ? m.appendChild(this.content) : m.appendChild(this.content.content), new s.P(this.placeholder).D(), document.body.insertBefore(e, document.body.firstChild), this.search.g(), this.location.G && !this.location.sandbox && this.M.g()
						}, e.prototype.j = function() {
							var e = this,
								t = a.o.create("div");
							t.className = "dp-menu";
							var n = a.o.create("ul"),
								i = this.location.O;
							return this.S.getItems(i).forEach((function(t) {
								var i = t.filename;
								"index.html" !== i || e.location.filesystem || (i = "./");
								var l = e._(t.text, i, t.filename === e.location.filename, t.H);
								n.appendChild(l.div)
							})), t.appendChild(n), t
						}, e.prototype._ = function(e, t, n, i) {
							var l = a.o.create("li");
							if ("string" == typeof t) {
								var r = document.createElement("a");
								if (r.href = t, r.title = e, n && (r.className = "active"), i) {
									var m = a.o.create("span");
									m.innerText = "NEW", m.className = "new", r.appendChild(m)
								}
								var o = a.o.create("span");
								o.innerText = e, r.appendChild(o), l.appendChild(r)
							} else {
								var s = a.o.create("strong");
								s.innerText = e, l.appendChild(s)
							}
							return {
								div: l
							}
						}, e.prototype.R = function() {
							var e = this,
								t = a.o.u(),
								n = this.location.A ? "" : "../",
								i = this.location.filesystem ? "index.html" : "",
								l = this.location.sandbox ? "Sandbox" : "Demo",
								r = this.J(l, n + i, this.location.A);
							return t.appendChild(r.div), this.S.L().forEach((function(l) {
								var a = "" + n + l.O + "/" + i,
									r = l.O === e.location.O,
									m = e.J(l.text, a, r);
								t.appendChild(m.div)
							})), t
						}, e.prototype.J = function(e, t, n) {
							var i = a.o.create("div");
							i.className = "dpw-header-item";
							var l = document.createElement("a");
							return l.href = t, l.innerText = e, n && (l.className = "dpw-header-item-selected"), i.appendChild(l), {
								div: i,
								a: l
							}
						}, e
					}();
				t.t = c
			},
			11: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.p = void 0;
				var n = function() {
					function e(e, t, n) {
						this.N = ["demo", "demo2", "sandbox"], this.pathname = n, this.host = t, this.protocol = e, this.W()
					}
					return Object.defineProperty(e.prototype, "A", {
						get: function() {
							return "ROOT" === this.O
						},
						V: !1,
						I: !0
					}), e.test = function() {}, e.prototype.W = function() {
						this.filename = this.B(this.pathname), this.O = this.F(this.pathname), this.sandbox = this.q(this.pathname), this.G = this.K(this.host), this.filesystem = this.$(this.protocol)
					}, e.prototype.B = function(e) {
						var t = e.substring(e.lastIndexOf("/") + 1);
						return "" === t && (t = "index.html"), t
					}, e.prototype.$ = function(e) {
						return "file:" === e
					}, e.prototype.F = function(e) {
						var t = e.lastIndexOf("/"),
							n = e.lastIndexOf("/", t - 1),
							i = e.substring(n + 1, t);
						return "/" === i && (i = "ROOT"), -1 !== this.N.indexOf(i) && (i = "ROOT"), i
					}, e.prototype.K = function(e) {
						return "javascript.daypilot.org" === e
					}, e.prototype.q = function(e) {
						return 0 === e.indexOf("/sandbox")
					}, e.prototype.print = function() {
						window.console.log(this.pathname, this.O, this.filename)
					}, e
				}();
				t.p = n
			},
			463: function(e, t, n) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.C = void 0;
				var i = n(143),
					l = function() {
						function e() {
							this.U = null
						}
						return e.prototype.g = function() {
							var e = this,
								t = this.Y = i.o.get("search-box-input");
							t.addEventListener("keyup", (function(n) {
								"Escape" !== n.key && "Esc" !== n.key || (t.value = ""), e.X(t.value)
							})), i.o.get("search-box-clear").addEventListener("click", (function(n) {
								e.Z(), t.focus()
							}))
						}, e.prototype.Z = function() {
							this.Y.value = "", this.X("")
						}, e.prototype.X = function(e) {
							var t = !e || "" === e.trim();
							t ? this.ee() : this.te(), i.o.query(".menu li").forEach((function(n) {
								var i = n.getElementsByTagName("a")[0],
									l = i && -1 !== i.innerText.toLowerCase().indexOf(e.toLowerCase()),
									a = t || l;
								n.style.display = a ? "" : "none"
							}))
						}, e.prototype.te = function() {
							if (null == this.U) {
								var e = this.ne();
								e && (this.U = e.offsetHeight, e.style.height = this.U + "px")
							}
						}, e.prototype.ee = function() {
							var e = this.ne();
							this.U = null, e && (e.style.height = "")
						}, e.prototype.ne = function() {
							return i.o.query(".dp-menu")[0]
						}, e
					}();
				t.C = l
			},
			151: function(e, t, n) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.P = void 0;
				var i = n(143),
					l = n(827),
					a = n(722),
					r = function() {
						function e(e) {
							this.placeholder = e
						}
						return e.prototype.D = function() {
							if (null !== this.ie()) {
								var e = this.le();
								this.placeholder.appendChild(e)
							}
						}, e.prototype.le = function() {
							var e = this,
								t = i.o.create("div");
							t.className = "space";
							var n = i.o.create("button");
							return n.className = "button-source", n.innerText = "Show source", n.onclick = function() {
								e.ae(), n.style.display = "none"
							}, t.appendChild(n), t
						}, e.prototype.ae = function() {
							var e, t = this.ie();
							a.l.m ? ((e = i.o.create("div")).innerHTML = "<pre>" + l.me.re(t.innerHTML) + "</pre>", this.placeholder.appendChild(e)) : ((e = i.o.create("pre")).innerText = l.me.re(t.innerText), this.placeholder.appendChild(e))
						}, e.prototype.ie = function() {
							return this.placeholder.querySelector("script:not([src])")
						}, e
					}();
				t.P = r
			},
			4: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.T = void 0;
				var n = [{
						text: "Calendar",
						O: "calendar",
						children: [{
							text: "Main"
						}, {
							text: "JavaScript Event Calendar",
							filename: "index.html"
						}, {
							text: "Navigation"
						}, {
							text: "Navigator",
							filename: "navigator.html"
						}, {
							text: "Date Picker",
							filename: "datepicker.html"
						}, {
							text: "Next/Previous",
							filename: "nextprevious.html"
						}, {
							text: "Themes"
						}, {
							text: "Green Theme",
							filename: "themegreen.html"
						}, {
							text: "Transparent Theme",
							filename: "themetransparent.html"
						}, {
							text: "White Theme",
							filename: "themewhite.html"
						}, {
							text: "Traditional Theme",
							filename: "themetraditional.html"
						}, {
							text: "Events"
						}, {
							text: "All-Day Events",
							filename: "allday.html"
						}, {
							text: "Active Areas",
							filename: "eventareas.html"
						}, {
							text: "Event Context Menu",
							filename: "eventmenu.html"
						}, {
							text: "Event Customization",
							filename: "eventcustomization.html"
						}, {
							text: "Event Deleting",
							filename: "eventdeleting.html"
						}, {
							text: "Event Filtering",
							filename: "eventfiltering.html"
						}, {
							text: "Event Moving Customization",
							filename: "eventmovingcustomization.html"
						}, {
							text: "Event Overlaps",
							filename: "eventoverlaps.html"
						}, {
							text: "Event Resizing Customization",
							filename: "eventresizingcustomization.html"
						}, {
							text: "Event Selecting",
							filename: "eventselecting.html"
						}, {
							text: "External Drag and Drop",
							filename: "external.html"
						}, {
							text: "Progressive Event Rendering",
							filename: "eventsprogressive.html",
							H: !0
						}, {
							text: "Resource Calendar"
						}, {
							text: "Column Filtering",
							filename: "columnfiltering.html"
						}, {
							text: "Column Moving",
							filename: "columnmoving.html",
							H: !0
						}, {
							text: "Column Resizing",
							filename: "columnresizing.html",
							H: !0
						}, {
							text: "Custom Column Width",
							filename: "columnwidth.html",
							H: !0
						}, {
							text: "Grid"
						}, {
							text: "50 columns",
							filename: "50columns.html"
						}, {
							text: "100% height",
							filename: "100pctheight.html"
						}, {
							text: "Crosshair",
							filename: "crosshair.html"
						}, {
							text: "Disabled Cells",
							filename: "cellsdisabled.html"
						}, {
							text: "Time Range Selecting",
							filename: "timerangeselecting.html"
						}, {
							text: "Localization"
						}, {
							text: "Localization",
							filename: "localization.html"
						}, {
							text: "RTL",
							filename: "rtl.html"
						}, {
							text: "Timeline"
						}, {
							text: "Overnight Shift",
							filename: "overnight.html"
						}, {
							text: "Lunch Break",
							filename: "lunchbreak.html"
						}, {
							text: "Time Header Cell Duration",
							filename: "timeheadercellduration.html"
						}, {
							text: "Export"
						}, {
							text: "JPEG Export",
							filename: "exportjpeg.html"
						}, {
							text: "SVG Export",
							filename: "exportsvg.html"
						}, {
							text: "API"
						}, {
							text: "Angular",
							filename: "angular2.html"
						}, {
							text: "AngularJS",
							filename: "angularjs.html"
						}, {
							text: "AngularJS (Controller As)",
							filename: "angularjsctrlas.html"
						}, {
							text: "jQuery",
							filename: "jquery.html"
						}, {
							text: "View Types"
						}, {
							text: "Day View",
							filename: "day.html"
						}, {
							text: "Week View",
							filename: "week.html"
						}, {
							text: "Resources",
							filename: "resources.html"
						}, {
							text: "Resource Hierarchy",
							filename: "hierarchy.html"
						}, {
							text: "Days-Resources",
							filename: "daysresources.html"
						}]
					}, {
						text: "Month",
						O: "month",
						children: [{
							text: "Main"
						}, {
							text: "JavaScript Monthly Event Calendar",
							filename: "index.html"
						}, {
							text: "Navigation"
						}, {
							text: "Navigator",
							filename: "navigator.html"
						}, {
							text: "Date Picker",
							filename: "datepicker.html"
						}, {
							text: "Next/Previous",
							filename: "nextprevious.html"
						}, {
							text: "Themes"
						}, {
							text: "Green Theme",
							filename: "themegreen.html"
						}, {
							text: "Transparent Theme",
							filename: "themetransparent.html"
						}, {
							text: "White Theme",
							filename: "themewhite.html"
						}, {
							text: "Traditional Theme",
							filename: "themetraditional.html"
						}, {
							text: "Events"
						}, {
							text: "Event Active Areas",
							filename: "eventareas.html"
						}, {
							text: "Event Context Menu",
							filename: "eventmenu.html"
						}, {
							text: "Event Deleting",
							filename: "eventdeleting.html"
						}, {
							text: "Event Filtering",
							filename: "eventfiltering.html"
						}, {
							text: "Event Customization",
							filename: "eventcustomization.html"
						}, {
							text: "Event Position",
							filename: "eventposition.html"
						}, {
							text: "Event Selecting",
							filename: "eventselecting.html"
						}, {
							text: "Event Start and End Time",
							filename: "eventstartend.html"
						}, {
							text: "Event End Spec",
							filename: "eventendspec.html"
						}, {
							text: "External Drag and Drop",
							filename: "external.html"
						}, {
							text: "Max Events",
							filename: "eventsmax.html",
							H: !0
						}, {
							text: "Grid"
						}, {
							text: "Disabled Cells",
							filename: "cellsdisabled.html",
							H: !0
						}, {
							text: "Highlighting Today",
							filename: "today.html"
						}, {
							text: "Export"
						}, {
							text: "JPEG Export",
							filename: "exportjpeg.html"
						}, {
							text: "SVG Export",
							filename: "exportsvg.html"
						}, {
							text: "API"
						}, {
							text: "Angular",
							filename: "angular2.html"
						}, {
							text: "AngularJS",
							filename: "angularjs.html"
						}, {
							text: "jQuery",
							filename: "jquery.html"
						}, {
							text: "Appearance"
						}, {
							text: "100% Height",
							filename: "100pctheight.html"
						}, {
							text: "View Types"
						}, {
							text: "Weeks",
							filename: "weeks.html"
						}, {
							text: "Localization"
						}, {
							text: "Localization",
							filename: "localization.html"
						}]
					}, {
						text: "Scheduler",
						O: "scheduler",
						children: [{
							text: "Main"
						}, {
							text: "JavaScript Scheduler",
							filename: "index.html"
						}, {
							text: "Themes"
						}, {
							text: "Transparent Theme",
							filename: "themetransparent.html"
						}, {
							text: "White Theme",
							filename: "themewhite.html"
						}, {
							text: "Green Theme",
							filename: "themegreen.html"
						}, {
							text: "Theme 8",
							filename: "theme8.html"
						}, {
							text: "Traditional Theme",
							filename: "themetraditional.html"
						}, {
							text: "Blue Theme",
							filename: "themeblue.html"
						}, {
							text: "Controls"
						}, {
							text: "Message Bar",
							filename: "messagebar.html"
						}, {
							text: "Navigation"
						}, {
							text: "Navigator",
							filename: "navigator.html"
						}, {
							text: "Next/Previous Buttons",
							filename: "nextprevious.html"
						}, {
							text: "Grid"
						}, {
							text: "Cell Customization",
							filename: "cellcustomization.html"
						}, {
							text: "Cell Width",
							filename: "cellwidth.html"
						}, {
							text: "Auto Cell Width",
							filename: "autocellwidth.html"
						}, {
							text: "Crosshair",
							filename: "crosshair.html"
						}, {
							text: "Disabled Cells",
							filename: "cellsdisabled.html"
						}, {
							text: "Highlighting Unavailable Cells",
							filename: "unavailable.html"
						}, {
							text: "Highlighting Weekends",
							filename: "highlighting.html"
						}, {
							text: "Infinite Scrolling",
							filename: "infinite.html"
						}, {
							text: "Keyboard Access",
							filename: "keyboard.html"
						}, {
							text: "Multi-Range Selecting",
							filename: "multirange.html"
						}, {
							text: "Scrolling",
							filename: "scrolling.html"
						}, {
							text: "Snap to Grid",
							filename: "snaptogrid.html"
						}, {
							text: "Time Range Selecting",
							filename: "timerangeselecting.html"
						}, {
							text: "Timeline"
						}, {
							text: "Active Areas",
							filename: "timeheaderactiveareas.html"
						}, {
							text: "Time Headers",
							filename: "timeheaders.html"
						}, {
							text: "Hiding Weekends",
							filename: "hide.html"
						}, {
							text: "Hiding Non-Business Hours",
							filename: "hidingnonbusiness.html"
						}, {
							text: "Separators",
							filename: "separators.html"
						}, {
							text: "Scale: Hours",
							filename: "scalehours.html"
						}, {
							text: "Scale: Days",
							filename: "scaledays.html"
						}, {
							text: "Scale: Weeks",
							filename: "scaleweeks.html"
						}, {
							text: "Scale: Months",
							filename: "scalemonths.html"
						}, {
							text: "Scale: Years",
							filename: "scaleyears.html"
						}, {
							text: "Scale: Custom",
							filename: "scalecustom.html"
						}, {
							text: "Export"
						}, {
							text: "SVG Export",
							filename: "exportsvg.html"
						}, {
							text: "PNG Export",
							filename: "exportpng.html"
						}, {
							text: "JPEG Export",
							filename: "exportjpg.html"
						}, {
							text: "Printing",
							filename: "exportprint.html"
						}, {
							text: "Gantt"
						}, {
							text: "Gantt",
							filename: "gantt.html"
						}, {
							text: "API"
						}, {
							text: "Angular",
							filename: "angular2.html"
						}, {
							text: "AngularJS",
							filename: "angularjs.html"
						}, {
							text: "AngularJS (Controller As)",
							filename: "angularjsctrlas.html"
						}, {
							text: "jQuery",
							filename: "jquery.html"
						}, {
							text: "Vue.js",
							filename: "vuejs.html"
						}, {
							text: "React",
							filename: "react.html"
						}, {
							text: "View Types"
						}, {
							text: "Timesheet",
							filename: "timesheet.html"
						}, {
							text: "Events"
						}, {
							text: "Asynchronous Validation",
							filename: "asyncvalidation.html"
						}, {
							text: "Group Concurrent Events",
							filename: "groupconcurrent.html"
						}, {
							text: "Dynamic Event Loading",
							filename: "dynamic.html"
						}, {
							text: "Event Active Areas",
							filename: "eventareas.html"
						}, {
							text: "Event Containers",
							filename: "eventcontainers.html"
						}, {
							text: "Event Context Menu",
							filename: "eventmenu.html"
						}, {
							text: "Event Copying",
							filename: "eventcopying.html"
						}, {
							text: "Event Customization",
							filename: "eventcustomization.html"
						}, {
							text: "Event Deleting",
							filename: "eventdeleting.html"
						}, {
							text: "Event Filtering",
							filename: "eventfiltering.html"
						}, {
							text: "Event Height",
							filename: "eventheight.html"
						}, {
							text: "Event Icons",
							filename: "eventicons.html"
						}, {
							text: "Event Inline Editing",
							filename: "eventinlineediting.html"
						}, {
							text: "Event Links",
							filename: "eventlinks.html"
						}, {
							text: "Event Loading",
							filename: "eventloading.html"
						}, {
							text: "Event Moving",
							filename: "eventmoving.html"
						}, {
							text: "Event Moving (Non-Business)",
							filename: "eventmovingnonbusiness.html"
						}, {
							text: "Event Moving between Schedulers",
							filename: "eventmovingtwoschedulers.html"
						}, {
							text: "Event Multi-Moving",
							filename: "eventmultimoving.html"
						}, {
							text: "Event Multi-Resizing",
							filename: "eventmultiresizing.html"
						}, {
							text: "Event Multi-Selecting",
							filename: "eventmultiselecting.html"
						}, {
							text: "Event Overlapping",
							filename: "eventoverlapping.html"
						}, {
							text: "Event Phases",
							filename: "eventphases.html"
						}, {
							text: "Event Searching",
							filename: "eventsearching.html"
						}, {
							text: "Event Selecting",
							filename: "eventselecting.html"
						}, {
							text: "Event Stacking Line Height",
							filename: "eventstackinglineheight.html"
						}, {
							text: "Event Versions",
							filename: "eventversions.html"
						}, {
							text: "External Drag and Drop",
							filename: "external.html"
						}, {
							text: "Joint Events",
							filename: "eventsjoint.html"
						}, {
							text: "Milestones",
							filename: "milestones.html"
						}, {
							text: "Percent Complete",
							filename: "eventcomplete.html"
						}, {
							text: "Queue",
							filename: "queue.html",
							H: !0
						}, {
							text: "Real-Time Drag and Drop Indicators",
							filename: "eventrealtime.html"
						}, {
							text: "Rows"
						}, {
							text: "Custom Event Height",
							filename: "roweventheight.html"
						}, {
							text: "Dynamic Tree Loading",
							filename: "treedynamic.html"
						}, {
							text: "Progressive Rendering",
							filename: "rowprogressive.html"
						}, {
							text: "Frozen Rows",
							filename: "rowsfrozen.html",
							H: !0
						}, {
							text: "Resource Tree",
							filename: "tree.html"
						}, {
							text: "Resource Utilization",
							filename: "resourceutilization.html"
						}, {
							text: "Row Creating",
							filename: "rowcreating.html"
						}, {
							text: "Row Editing",
							filename: "rowediting.html"
						}, {
							text: "Row Filtering",
							filename: "rowfiltering.html"
						}, {
							text: "Row Header Active Areas",
							filename: "rowheaderactiveareas.html"
						}, {
							text: "Row Header Columns",
							filename: "rowheadercolumns.html"
						}, {
							text: "Row Header Hiding",
							filename: "rowheaderhiding.html"
						}, {
							text: "Row Header Scrolling",
							filename: "rowheaderscrolling.html"
						}, {
							text: "Row Moving",
							filename: "rowmoving.html"
						}, {
							text: "Row Selecting",
							filename: "rowselecting.html"
						}, {
							text: "Row Sorting",
							filename: "rowsorting.html"
						}, {
							text: "Split Resources",
							filename: "split.html",
							H: !0
						}, {
							text: "Localization"
						}, {
							text: "Localization",
							filename: "localization.html"
						}]
					}, {
						text: "Gantt",
						O: "gantt",
						children: [{
							text: "Main"
						}, {
							text: "JavaScript Gantt",
							filename: "index.html"
						}, {
							text: "Rows"
						}, {
							text: "Row Selecting",
							filename: "rowselecting.html"
						}, {
							text: "Tasks"
						}, {
							text: "Task Bubble",
							filename: "taskbubble.html"
						}, {
							text: "Task Creating",
							filename: "taskcreating.html"
						}, {
							text: "Task Resizing",
							filename: "taskresizing.html"
						}, {
							text: "Task Versions",
							filename: "taskversions.html"
						}, {
							text: "Task Moving (Two Gantt Charts)",
							filename: "taskmovingtwoganttcharts.html"
						}, {
							text: "Progressive Rendering",
							filename: "progressive.html"
						}, {
							text: "Export"
						}, {
							text: "PNG Export",
							filename: "exportpng.html"
						}, {
							text: "SVG Export",
							filename: "exportsvg.html"
						}, {
							text: "Links"
						}, {
							text: "Links",
							filename: "links.html"
						}, {
							text: "Grid"
						}, {
							text: "Auto Cell Width",
							filename: "autocellwidth.html"
						}, {
							text: "API"
						}, {
							text: "Angular",
							filename: "angular2.html"
						}, {
							text: "AngularJS",
							filename: "angularjs.html"
						}, {
							text: "jQuery",
							filename: "jquery.html"
						}]
					}, {
						text: "Kanban",
						O: "kanban",
						children: [{
							text: "JavaScript Kanban",
							filename: "index.html"
						}, {
							text: "Columns"
						}, {
							text: "Column Active Areas",
							filename: "columnactiveareas.html"
						}, {
							text: "Column Moving",
							filename: "columnmoving.html"
						}, {
							text: "Fixed Column Width",
							filename: "columnfixedwidth.html"
						}, {
							text: "Cards"
						}, {
							text: "Card Active Areas",
							filename: "cardactiveareas.html"
						}, {
							text: "Card Auto Height",
							filename: "cardautoheight.html"
						}, {
							text: "Card Context Menu",
							filename: "cardcontextmenu.html"
						}, {
							text: "Card Creating",
							filename: "cardcreating.html"
						}, {
							text: "Card CSS",
							filename: "cardcss.html"
						}, {
							text: "Card Deleting",
							filename: "carddeleting.html"
						}, {
							text: "Card Moving",
							filename: "cardmoving.html"
						}, {
							text: "External Drag and Drop",
							filename: "external.html"
						}, {
							text: "Swimlanes"
						}, {
							text: "Swimlanes",
							filename: "swimlanes.html"
						}, {
							text: "Swimlane Moving",
							filename: "swimlanemoving.html"
						}, {
							text: "Swimlane Collapsing",
							filename: "swimlanecollapsing.html"
						}, {
							text: "API"
						}, {
							text: "Angular",
							filename: "angular2.html"
						}, {
							text: "AngularJS",
							filename: "angularjs.html"
						}]
					}],
					i = function() {
						function e() {
							this.oe = {}, this.load()
						}
						return e.prototype.getItems = function(e) {
							var t, n;
							return null !== (n = null === (t = this.oe[e]) || void 0 === t ? void 0 : t.children) && void 0 !== n ? n : []
						}, e.prototype.L = function() {
							return n
						}, e.prototype.load = function() {
							var e = this;
							n.forEach((function(t) {
								e.oe[t.O] = t
							}))
						}, e
					}();
				t.T = i
			},
			827: function(e, t) {
				Object.defineProperty(t, "i", {
					value: !0
				}), t.me = void 0;
				var n = function() {
					function e() {}
					return e.re = function(e) {
						var t = this,
							n = e.split(/\r?\n/),
							i = 100;
						n.forEach((function(e) {
							if (!(0 === e.trim().length)) {
								var n = t.se(e);
								n < i && (i = n)
							}
						}));
						var l = n.map((function(e) {
								return 0 === e.trim().length ? "" : e.substring(i)
							})),
							a = !1;
						return l.filter((function(e) {
							var t = 0 === e.trim().length;
							return !(!a && t) && (a = !0, !0)
						})).join("\n")
					}, e.se = function(t) {
						var n = 0;
						return e.he(t).some((function(e) {
							if (" " !== e) return !0;
							n += 1
						})), n
					}, e.he = function(e) {
						return e.split("")
					}, e
				}();
				t.me = n
			}
		},
		t = {};

	function n(i) {
		if (t[i]) return t[i].ce;
		var l = t[i] = {
			ce: {}
		};
		return e[i](l, l.ce, n), l.ce
	}
	n.r = function(e) {
		"undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
			value: "Module"
		}), Object.defineProperty(e, "i", {
			value: !0
		})
	}, n(752)
}();