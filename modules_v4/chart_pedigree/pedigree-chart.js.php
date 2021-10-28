<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

 if (!defined('KT_KIWITREES')) {
 	header('HTTP/1.0 403 Forbidden');
 	exit;
 }

 //global $KT_THEME;

?>

<script>

	var theme = "<?php echo KT_THEME_DIR; ?>";

	 ! function(t, n) {
	     "object" == typeof exports && "undefined" != typeof module ? n(exports) : "function" == typeof define && define.amd ? define(["exports"], n) : n((t = t || self).rso = {})
	 }(this, function(t) {
	     "use strict";
	     var n = "http://www.w3.org/1999/xhtml",
	         e = {
	             svg: "http://www.w3.org/2000/svg",
	             xhtml: n,
	             xlink: "http://www.w3.org/1999/xlink",
	             xml: "http://www.w3.org/XML/1998/namespace",
	             xmlns: "http://www.w3.org/2000/xmlns/"
	         };

	     function r(t) {
	         var n = t += "",
	             r = n.indexOf(":");
	         return r >= 0 && "xmlns" !== (n = t.slice(0, r)) && (t = t.slice(r + 1)), e.hasOwnProperty(n) ? {
	             space: e[n],
	             local: t
	         } : t
	     }

	     function i(t) {
	         var e = r(t);
	         return (e.local ? function(t) {
	             return function() {
	                 return this.ownerDocument.createElementNS(t.space, t.local)
	             }
	         } : function(t) {
	             return function() {
	                 var e = this.ownerDocument,
	                     r = this.namespaceURI;
	                 return r === n && e.documentElement.namespaceURI === n ? e.createElement(t) : e.createElementNS(r, t)
	             }
	         })(e)
	     }

	     function o() {}

	     function u(t) {
	         return null == t ? o : function() {
	             return this.querySelector(t)
	         }
	     }

	     function a() {
	         return []
	     }

	     function s(t) {
	         return null == t ? a : function() {
	             return this.querySelectorAll(t)
	         }
	     }

	     function c(t) {
	         return function() {
	             return this.matches(t)
	         }
	     }

	     function h(t) {
	         return new Array(t.length)
	     }

	     function l(t, n) {
	         this.ownerDocument = t.ownerDocument, this.namespaceURI = t.namespaceURI, this._next = null, this._parent = t, this.__data__ = n
	     }
	     l.prototype = {
	         constructor: l,
	         appendChild: function(t) {
	             return this._parent.insertBefore(t, this._next)
	         },
	         insertBefore: function(t, n) {
	             return this._parent.insertBefore(t, n)
	         },
	         querySelector: function(t) {
	             return this._parent.querySelector(t)
	         },
	         querySelectorAll: function(t) {
	             return this._parent.querySelectorAll(t)
	         }
	     };
	     var f = "$";

	     function p(t, n, e, r, i, o) {
	         for (var u, a = 0, s = n.length, c = o.length; a < c; ++a)(u = n[a]) ? (u.__data__ = o[a], r[a] = u) : e[a] = new l(t, o[a]);
	         for (; a < s; ++a)(u = n[a]) && (i[a] = u)
	     }

	     function d(t, n, e, r, i, o, u) {
	         var a, s, c, h = {},
	             p = n.length,
	             d = o.length,
	             g = new Array(p);
	         for (a = 0; a < p; ++a)(s = n[a]) && (g[a] = c = f + u.call(s, s.__data__, a, n), c in h ? i[a] = s : h[c] = s);
	         for (a = 0; a < d; ++a)(s = h[c = f + u.call(t, o[a], a, o)]) ? (r[a] = s, s.__data__ = o[a], h[c] = null) : e[a] = new l(t, o[a]);
	         for (a = 0; a < p; ++a)(s = n[a]) && h[g[a]] === s && (i[a] = s)
	     }

	     function g(t, n) {
	         return t < n ? -1 : t > n ? 1 : t >= n ? 0 : NaN
	     }

	     function v(t) {
	         return t.ownerDocument && t.ownerDocument.defaultView || t.document && t || t.defaultView
	     }

	     function m(t, n) {
	         return t.style.getPropertyValue(n) || v(t).getComputedStyle(t, null).getPropertyValue(n)
	     }

	     function y(t) {
	         return t.trim().split(/^|\s+/)
	     }

	     function _(t) {
	         return t.classList || new w(t)
	     }

	     function w(t) {
	         this._node = t, this._names = y(t.getAttribute("class") || "")
	     }

	     function x(t, n) {
	         for (var e = _(t), r = -1, i = n.length; ++r < i;) e.add(n[r])
	     }

	     function b(t, n) {
	         for (var e = _(t), r = -1, i = n.length; ++r < i;) e.remove(n[r])
	     }

	     function M() {
	         this.textContent = ""
	     }

	     function T() {
	         this.innerHTML = ""
	     }

	     function C() {
	         this.nextSibling && this.parentNode.appendChild(this)
	     }

	     function k() {
	         this.previousSibling && this.parentNode.insertBefore(this, this.parentNode.firstChild)
	     }

	     function A() {
	         return null
	     }

	     function z() {
	         var t = this.parentNode;
	         t && t.removeChild(this)
	     }

	     function N() {
	         return this.parentNode.insertBefore(this.cloneNode(!1), this.nextSibling)
	     }

	     function S() {
	         return this.parentNode.insertBefore(this.cloneNode(!0), this.nextSibling)
	     }
	     w.prototype = {
	         add: function(t) {
	             this._names.indexOf(t) < 0 && (this._names.push(t), this._node.setAttribute("class", this._names.join(" ")))
	         },
	         remove: function(t) {
	             var n = this._names.indexOf(t);
	             n >= 0 && (this._names.splice(n, 1), this._node.setAttribute("class", this._names.join(" ")))
	         },
	         contains: function(t) {
	             return this._names.indexOf(t) >= 0
	         }
	     };
	     var U = {},
	         D = null;
	     "undefined" != typeof document && ("onmouseenter" in document.documentElement || (U = {
	         mouseenter: "mouseover",
	         mouseleave: "mouseout"
	     }));

	     function E(t, n, e) {
	         return t = Y(t, n, e),
	             function(n) {
	                 var e = n.relatedTarget;
	                 e && (e === this || 8 & e.compareDocumentPosition(this)) || t.call(this, n)
	             }
	     }

	     function Y(t, n, e) {
	         return function(r) {
	             var i = D;
	             D = r;
	             try {
	                 t.call(this, this.__data__, n, e)
	             } finally {
	                 D = i
	             }
	         }
	     }

	     function P(t) {
	         return function() {
	             var n = this.__on;
	             if (n) {
	                 for (var e, r = 0, i = -1, o = n.length; r < o; ++r) e = n[r], t.type && e.type !== t.type || e.name !== t.name ? n[++i] = e : this.removeEventListener(e.type, e.listener, e.capture);
	                 ++i ? n.length = i : delete this.__on
	             }
	         }
	     }

	     function H(t, n, e) {
	         var r = U.hasOwnProperty(t.type) ? E : Y;
	         return function(i, o, u) {
	             var a, s = this.__on,
	                 c = r(n, o, u);
	             if (s)
	                 for (var h = 0, l = s.length; h < l; ++h)
	                     if ((a = s[h]).type === t.type && a.name === t.name) return this.removeEventListener(a.type, a.listener, a.capture), this.addEventListener(a.type, a.listener = c, a.capture = e), void(a.value = n);
	             this.addEventListener(t.type, c, e), a = {
	                 type: t.type,
	                 name: t.name,
	                 value: n,
	                 listener: c,
	                 capture: e
	             }, s ? s.push(a) : this.__on = [a]
	         }
	     }

	     function F(t, n, e) {
	         var r = v(t),
	             i = r.CustomEvent;
	         "function" == typeof i ? i = new i(n, e) : (i = r.document.createEvent("Event"), e ? (i.initEvent(n, e.bubbles, e.cancelable), i.detail = e.detail) : i.initEvent(n, !1, !1)), t.dispatchEvent(i)
	     }
	     var j = [null];

	     function B(t, n) {
	         this._groups = t, this._parents = n
	     }

	     function L() {
	         return new B([
	             [document.documentElement]
	         ], j)
	     }

	     function X(t) {
	         return "string" == typeof t ? new B([
	             [document.querySelector(t)]
	         ], [document.documentElement]) : new B([
	             [t]
	         ], j)
	     }

	     function I() {
	         for (var t, n = D; t = n.sourceEvent;) n = t;
	         return n
	     }

	     function $(t, n) {
	         var e = t.ownerSVGElement || t;
	         if (e.createSVGPoint) {
	             var r = e.createSVGPoint();
	             return r.x = n.clientX, r.y = n.clientY, [(r = r.matrixTransform(t.getScreenCTM().inverse())).x, r.y]
	         }
	         var i = t.getBoundingClientRect();
	         return [n.clientX - i.left - t.clientLeft, n.clientY - i.top - t.clientTop]
	     }

	     function O(t) {
	         var n = I();
	         return n.changedTouches && (n = n.changedTouches[0]), $(t, n)
	     }

	     function q(t, n, e) {
	         arguments.length < 3 && (e = n, n = I().changedTouches);
	         for (var r, i = 0, o = n ? n.length : 0; i < o; ++i)
	             if ((r = n[i]).identifier === e) return $(t, r);
	         return null
	     }
	     B.prototype = L.prototype = {
	         constructor: B,
	         select: function(t) {
	             "function" != typeof t && (t = u(t));
	             for (var n = this._groups, e = n.length, r = new Array(e), i = 0; i < e; ++i)
	                 for (var o, a, s = n[i], c = s.length, h = r[i] = new Array(c), l = 0; l < c; ++l)(o = s[l]) && (a = t.call(o, o.__data__, l, s)) && ("__data__" in o && (a.__data__ = o.__data__), h[l] = a);
	             return new B(r, this._parents)
	         },
	         selectAll: function(t) {
	             "function" != typeof t && (t = s(t));
	             for (var n = this._groups, e = n.length, r = [], i = [], o = 0; o < e; ++o)
	                 for (var u, a = n[o], c = a.length, h = 0; h < c; ++h)(u = a[h]) && (r.push(t.call(u, u.__data__, h, a)), i.push(u));
	             return new B(r, i)
	         },
	         filter: function(t) {
	             "function" != typeof t && (t = c(t));
	             for (var n = this._groups, e = n.length, r = new Array(e), i = 0; i < e; ++i)
	                 for (var o, u = n[i], a = u.length, s = r[i] = [], h = 0; h < a; ++h)(o = u[h]) && t.call(o, o.__data__, h, u) && s.push(o);
	             return new B(r, this._parents)
	         },
	         data: function(t, n) {
	             if (!t) return v = new Array(this.size()), h = -1, this.each(function(t) {
	                 v[++h] = t
	             }), v;
	             var e, r = n ? d : p,
	                 i = this._parents,
	                 o = this._groups;
	             "function" != typeof t && (e = t, t = function() {
	                 return e
	             });
	             for (var u = o.length, a = new Array(u), s = new Array(u), c = new Array(u), h = 0; h < u; ++h) {
	                 var l = i[h],
	                     f = o[h],
	                     g = f.length,
	                     v = t.call(l, l && l.__data__, h, i),
	                     m = v.length,
	                     y = s[h] = new Array(m),
	                     _ = a[h] = new Array(m);
	                 r(l, f, y, _, c[h] = new Array(g), v, n);
	                 for (var w, x, b = 0, M = 0; b < m; ++b)
	                     if (w = y[b]) {
	                         for (b >= M && (M = b + 1); !(x = _[M]) && ++M < m;);
	                         w._next = x || null
	                     }
	             }
	             return (a = new B(a, i))._enter = s, a._exit = c, a
	         },
	         enter: function() {
	             return new B(this._enter || this._groups.map(h), this._parents)
	         },
	         exit: function() {
	             return new B(this._exit || this._groups.map(h), this._parents)
	         },
	         join: function(t, n, e) {
	             var r = this.enter(),
	                 i = this,
	                 o = this.exit();
	             return r = "function" == typeof t ? t(r) : r.append(t + ""), null != n && (i = n(i)), null == e ? o.remove() : e(o), r && i ? r.merge(i).order() : i
	         },
	         merge: function(t) {
	             for (var n = this._groups, e = t._groups, r = n.length, i = e.length, o = Math.min(r, i), u = new Array(r), a = 0; a < o; ++a)
	                 for (var s, c = n[a], h = e[a], l = c.length, f = u[a] = new Array(l), p = 0; p < l; ++p)(s = c[p] || h[p]) && (f[p] = s);
	             for (; a < r; ++a) u[a] = n[a];
	             return new B(u, this._parents)
	         },
	         order: function() {
	             for (var t = this._groups, n = -1, e = t.length; ++n < e;)
	                 for (var r, i = t[n], o = i.length - 1, u = i[o]; --o >= 0;)(r = i[o]) && (u && 4 ^ r.compareDocumentPosition(u) && u.parentNode.insertBefore(r, u), u = r);
	             return this
	         },
	         sort: function(t) {
	             function n(n, e) {
	                 return n && e ? t(n.__data__, e.__data__) : !n - !e
	             }
	             t || (t = g);
	             for (var e = this._groups, r = e.length, i = new Array(r), o = 0; o < r; ++o) {
	                 for (var u, a = e[o], s = a.length, c = i[o] = new Array(s), h = 0; h < s; ++h)(u = a[h]) && (c[h] = u);
	                 c.sort(n)
	             }
	             return new B(i, this._parents).order()
	         },
	         call: function() {
	             var t = arguments[0];
	             return arguments[0] = this, t.apply(null, arguments), this
	         },
	         nodes: function() {
	             var t = new Array(this.size()),
	                 n = -1;
	             return this.each(function() {
	                 t[++n] = this
	             }), t
	         },
	         node: function() {
	             for (var t = this._groups, n = 0, e = t.length; n < e; ++n)
	                 for (var r = t[n], i = 0, o = r.length; i < o; ++i) {
	                     var u = r[i];
	                     if (u) return u
	                 }
	             return null
	         },
	         size: function() {
	             var t = 0;
	             return this.each(function() {
	                 ++t
	             }), t
	         },
	         empty: function() {
	             return !this.node()
	         },
	         each: function(t) {
	             for (var n = this._groups, e = 0, r = n.length; e < r; ++e)
	                 for (var i, o = n[e], u = 0, a = o.length; u < a; ++u)(i = o[u]) && t.call(i, i.__data__, u, o);
	             return this
	         },
	         attr: function(t, n) {
	             var e = r(t);
	             if (arguments.length < 2) {
	                 var i = this.node();
	                 return e.local ? i.getAttributeNS(e.space, e.local) : i.getAttribute(e)
	             }
	             return this.each((null == n ? e.local ? function(t) {
	                 return function() {
	                     this.removeAttributeNS(t.space, t.local)
	                 }
	             } : function(t) {
	                 return function() {
	                     this.removeAttribute(t)
	                 }
	             } : "function" == typeof n ? e.local ? function(t, n) {
	                 return function() {
	                     var e = n.apply(this, arguments);
	                     null == e ? this.removeAttributeNS(t.space, t.local) : this.setAttributeNS(t.space, t.local, e)
	                 }
	             } : function(t, n) {
	                 return function() {
	                     var e = n.apply(this, arguments);
	                     null == e ? this.removeAttribute(t) : this.setAttribute(t, e)
	                 }
	             } : e.local ? function(t, n) {
	                 return function() {
	                     this.setAttributeNS(t.space, t.local, n)
	                 }
	             } : function(t, n) {
	                 return function() {
	                     this.setAttribute(t, n)
	                 }
	             })(e, n))
	         },
	         style: function(t, n, e) {
	             return arguments.length > 1 ? this.each((null == n ? function(t) {
	                 return function() {
	                     this.style.removeProperty(t)
	                 }
	             } : "function" == typeof n ? function(t, n, e) {
	                 return function() {
	                     var r = n.apply(this, arguments);
	                     null == r ? this.style.removeProperty(t) : this.style.setProperty(t, r, e)
	                 }
	             } : function(t, n, e) {
	                 return function() {
	                     this.style.setProperty(t, n, e)
	                 }
	             })(t, n, null == e ? "" : e)) : m(this.node(), t)
	         },
	         property: function(t, n) {
	             return arguments.length > 1 ? this.each((null == n ? function(t) {
	                 return function() {
	                     delete this[t]
	                 }
	             } : "function" == typeof n ? function(t, n) {
	                 return function() {
	                     var e = n.apply(this, arguments);
	                     null == e ? delete this[t] : this[t] = e
	                 }
	             } : function(t, n) {
	                 return function() {
	                     this[t] = n
	                 }
	             })(t, n)) : this.node()[t]
	         },
	         classed: function(t, n) {
	             var e = y(t + "");
	             if (arguments.length < 2) {
	                 for (var r = _(this.node()), i = -1, o = e.length; ++i < o;)
	                     if (!r.contains(e[i])) return !1;
	                 return !0
	             }
	             return this.each(("function" == typeof n ? function(t, n) {
	                 return function() {
	                     (n.apply(this, arguments) ? x : b)(this, t)
	                 }
	             } : n ? function(t) {
	                 return function() {
	                     x(this, t)
	                 }
	             } : function(t) {
	                 return function() {
	                     b(this, t)
	                 }
	             })(e, n))
	         },
	         text: function(t) {
	             return arguments.length ? this.each(null == t ? M : ("function" == typeof t ? function(t) {
	                 return function() {
	                     var n = t.apply(this, arguments);
	                     this.textContent = null == n ? "" : n
	                 }
	             } : function(t) {
	                 return function() {
	                     this.textContent = t
	                 }
	             })(t)) : this.node().textContent
	         },
	         html: function(t) {
	             return arguments.length ? this.each(null == t ? T : ("function" == typeof t ? function(t) {
	                 return function() {
	                     var n = t.apply(this, arguments);
	                     this.innerHTML = null == n ? "" : n
	                 }
	             } : function(t) {
	                 return function() {
	                     this.innerHTML = t
	                 }
	             })(t)) : this.node().innerHTML
	         },
	         raise: function() {
	             return this.each(C)
	         },
	         lower: function() {
	             return this.each(k)
	         },
	         append: function(t) {
	             var n = "function" == typeof t ? t : i(t);
	             return this.select(function() {
	                 return this.appendChild(n.apply(this, arguments))
	             })
	         },
	         insert: function(t, n) {
	             var e = "function" == typeof t ? t : i(t),
	                 r = null == n ? A : "function" == typeof n ? n : u(n);
	             return this.select(function() {
	                 return this.insertBefore(e.apply(this, arguments), r.apply(this, arguments) || null)
	             })
	         },
	         remove: function() {
	             return this.each(z)
	         },
	         clone: function(t) {
	             return this.select(t ? S : N)
	         },
	         datum: function(t) {
	             return arguments.length ? this.property("__data__", t) : this.node().__data__
	         },
	         on: function(t, n, e) {
	             var r, i, o = function(t) {
	                     return t.trim().split(/^|\s+/).map(function(t) {
	                         var n = "",
	                             e = t.indexOf(".");
	                         return e >= 0 && (n = t.slice(e + 1), t = t.slice(0, e)), {
	                             type: t,
	                             name: n
	                         }
	                     })
	                 }(t + ""),
	                 u = o.length;
	             if (!(arguments.length < 2)) {
	                 for (a = n ? H : P, null == e && (e = !1), r = 0; r < u; ++r) this.each(a(o[r], n, e));
	                 return this
	             }
	             var a = this.node().__on;
	             if (a)
	                 for (var s, c = 0, h = a.length; c < h; ++c)
	                     for (r = 0, s = a[c]; r < u; ++r)
	                         if ((i = o[r]).type === s.type && i.name === s.name) return s.value
	         },
	         dispatch: function(t, n) {
	             return this.each(("function" == typeof n ? function(t, n) {
	                 return function() {
	                     return F(this, t, n.apply(this, arguments))
	                 }
	             } : function(t, n) {
	                 return function() {
	                     return F(this, t, n)
	                 }
	             })(t, n))
	         }
	     };
	     var V = {
	         value: function() {}
	     };

	     function R() {
	         for (var t, n = 0, e = arguments.length, r = {}; n < e; ++n) {
	             if (!(t = arguments[n] + "") || t in r) throw new Error("illegal type: " + t);
	             r[t] = []
	         }
	         return new W(r)
	     }

	     function W(t) {
	         this._ = t
	     }

	     function Z(t, n) {
	         for (var e, r = 0, i = t.length; r < i; ++r)
	             if ((e = t[r]).name === n) return e.value
	     }

	     function Q(t, n, e) {
	         for (var r = 0, i = t.length; r < i; ++r)
	             if (t[r].name === n) {
	                 t[r] = V, t = t.slice(0, r).concat(t.slice(r + 1));
	                 break
	             }
	         return null != e && t.push({
	             name: n,
	             value: e
	         }), t
	     }
	     W.prototype = R.prototype = {
	         constructor: W,
	         on: function(t, n) {
	             var e, r, i = this._,
	                 o = (r = i, (t + "").trim().split(/^|\s+/).map(function(t) {
	                     var n = "",
	                         e = t.indexOf(".");
	                     if (e >= 0 && (n = t.slice(e + 1), t = t.slice(0, e)), t && !r.hasOwnProperty(t)) throw new Error("unknown type: " + t);
	                     return {
	                         type: t,
	                         name: n
	                     }
	                 })),
	                 u = -1,
	                 a = o.length;
	             if (!(arguments.length < 2)) {
	                 if (null != n && "function" != typeof n) throw new Error("invalid callback: " + n);
	                 for (; ++u < a;)
	                     if (e = (t = o[u]).type) i[e] = Q(i[e], t.name, n);
	                     else if (null == n)
	                     for (e in i) i[e] = Q(i[e], t.name, null);
	                 return this
	             }
	             for (; ++u < a;)
	                 if ((e = (t = o[u]).type) && (e = Z(i[e], t.name))) return e
	         },
	         copy: function() {
	             var t = {},
	                 n = this._;
	             for (var e in n) t[e] = n[e].slice();
	             return new W(t)
	         },
	         call: function(t, n) {
	             if ((e = arguments.length - 2) > 0)
	                 for (var e, r, i = new Array(e), o = 0; o < e; ++o) i[o] = arguments[o + 2];
	             if (!this._.hasOwnProperty(t)) throw new Error("unknown type: " + t);
	             for (o = 0, e = (r = this._[t]).length; o < e; ++o) r[o].value.apply(n, i)
	         },
	         apply: function(t, n, e) {
	             if (!this._.hasOwnProperty(t)) throw new Error("unknown type: " + t);
	             for (var r = this._[t], i = 0, o = r.length; i < o; ++i) r[i].value.apply(n, e)
	         }
	     };
	     var J, G, K = 0,
	         tt = 0,
	         nt = 0,
	         et = 1e3,
	         rt = 0,
	         it = 0,
	         ot = 0,
	         ut = "object" == typeof performance && performance.now ? performance : Date,
	         at = "object" == typeof window && window.requestAnimationFrame ? window.requestAnimationFrame.bind(window) : function(t) {
	             setTimeout(t, 17)
	         };

	     function st() {
	         return it || (at(ct), it = ut.now() + ot)
	     }

	     function ct() {
	         it = 0
	     }

	     function ht() {
	         this._call = this._time = this._next = null
	     }

	     function lt(t, n, e) {
	         var r = new ht;
	         return r.restart(t, n, e), r
	     }

	     function ft() {
	         it = (rt = ut.now()) + ot, K = tt = 0;
	         try {
	             ! function() {
	                 st(), ++K;
	                 for (var t, n = J; n;)(t = it - n._time) >= 0 && n._call.call(null, t), n = n._next;
	                 --K
	             }()
	         } finally {
	             K = 0,
	                 function() {
	                     var t, n, e = J,
	                         r = 1 / 0;
	                     for (; e;) e._call ? (r > e._time && (r = e._time), t = e, e = e._next) : (n = e._next, e._next = null, e = t ? t._next = n : J = n);
	                     G = t, dt(r)
	                 }(), it = 0
	         }
	     }

	     function pt() {
	         var t = ut.now(),
	             n = t - rt;
	         n > et && (ot -= n, rt = t)
	     }

	     function dt(t) {
	         K || (tt && (tt = clearTimeout(tt)), t - it > 24 ? (t < 1 / 0 && (tt = setTimeout(ft, t - ut.now() - ot)), nt && (nt = clearInterval(nt))) : (nt || (rt = ut.now(), nt = setInterval(pt, et)), K = 1, at(ft)))
	     }

	     function gt(t, n, e) {
	         var r = new ht;
	         return n = null == n ? 0 : +n, r.restart(function(e) {
	             r.stop(), t(e + n)
	         }, n, e), r
	     }
	     ht.prototype = lt.prototype = {
	         constructor: ht,
	         restart: function(t, n, e) {
	             if ("function" != typeof t) throw new TypeError("callback is not a function");
	             e = (null == e ? st() : +e) + (null == n ? 0 : +n), this._next || G === this || (G ? G._next = this : J = this, G = this), this._call = t, this._time = e, dt()
	         },
	         stop: function() {
	             this._call && (this._call = null, this._time = 1 / 0, dt())
	         }
	     };
	     var vt = R("start", "end", "cancel", "interrupt"),
	         mt = [],
	         yt = 0,
	         _t = 1,
	         wt = 2,
	         xt = 3,
	         bt = 4,
	         Mt = 5,
	         Tt = 6;

	     function Ct(t, n, e, r, i, o) {
	         var u = t.__transition;
	         if (u) {
	             if (e in u) return
	         } else t.__transition = {};
	         ! function(t, n, e) {
	             var r, i = t.__transition;

	             function o(s) {
	                 var c, h, l, f;
	                 if (e.state !== _t) return a();
	                 for (c in i)
	                     if ((f = i[c]).name === e.name) {
	                         if (f.state === xt) return gt(o);
	                         f.state === bt ? (f.state = Tt, f.timer.stop(), f.on.call("interrupt", t, t.__data__, f.index, f.group), delete i[c]) : +c < n && (f.state = Tt, f.timer.stop(), f.on.call("cancel", t, t.__data__, f.index, f.group), delete i[c])
	                     }
	                 if (gt(function() {
	                         e.state === xt && (e.state = bt, e.timer.restart(u, e.delay, e.time), u(s))
	                     }), e.state = wt, e.on.call("start", t, t.__data__, e.index, e.group), e.state === wt) {
	                     for (e.state = xt, r = new Array(l = e.tween.length), c = 0, h = -1; c < l; ++c)(f = e.tween[c].value.call(t, t.__data__, e.index, e.group)) && (r[++h] = f);
	                     r.length = h + 1
	                 }
	             }

	             function u(n) {
	                 for (var i = n < e.duration ? e.ease.call(null, n / e.duration) : (e.timer.restart(a), e.state = Mt, 1), o = -1, u = r.length; ++o < u;) r[o].call(t, i);
	                 e.state === Mt && (e.on.call("end", t, t.__data__, e.index, e.group), a())
	             }

	             function a() {
	                 for (var r in e.state = Tt, e.timer.stop(), delete i[n], i) return;
	                 delete t.__transition
	             }
	             i[n] = e, e.timer = lt(function(t) {
	                 e.state = _t, e.timer.restart(o, e.delay, e.time), e.delay <= t && o(t - e.delay)
	             }, 0, e.time)
	         }(t, e, {
	             name: n,
	             index: r,
	             group: i,
	             on: vt,
	             tween: mt,
	             time: o.time,
	             delay: o.delay,
	             duration: o.duration,
	             ease: o.ease,
	             timer: null,
	             state: yt
	         })
	     }

	     function kt(t, n) {
	         var e = zt(t, n);
	         if (e.state > yt) throw new Error("too late; already scheduled");
	         return e
	     }

	     function At(t, n) {
	         var e = zt(t, n);
	         if (e.state > xt) throw new Error("too late; already running");
	         return e
	     }

	     function zt(t, n) {
	         var e = t.__transition;
	         if (!e || !(e = e[n])) throw new Error("transition not found");
	         return e
	     }

	     function Nt(t, n) {
	         var e, r, i, o = t.__transition,
	             u = !0;
	         if (o) {
	             for (i in n = null == n ? null : n + "", o)(e = o[i]).name === n ? (r = e.state > wt && e.state < Mt, e.state = Tt, e.timer.stop(), e.on.call(r ? "interrupt" : "cancel", t, t.__data__, e.index, e.group), delete o[i]) : u = !1;
	             u && delete t.__transition
	         }
	     }

	     function St(t, n, e) {
	         t.prototype = n.prototype = e, e.constructor = t
	     }

	     function Ut(t, n) {
	         var e = Object.create(t.prototype);
	         for (var r in n) e[r] = n[r];
	         return e
	     }

	     function Dt() {}
	     var Et = "\\s*([+-]?\\d+)\\s*",
	         Yt = "\\s*([+-]?\\d*\\.?\\d+(?:[eE][+-]?\\d+)?)\\s*",
	         Pt = "\\s*([+-]?\\d*\\.?\\d+(?:[eE][+-]?\\d+)?)%\\s*",
	         Ht = /^#([0-9a-f]{3})$/,
	         Ft = /^#([0-9a-f]{6})$/,
	         jt = new RegExp("^rgb\\(" + [Et, Et, Et] + "\\)$"),
	         Bt = new RegExp("^rgb\\(" + [Pt, Pt, Pt] + "\\)$"),
	         Lt = new RegExp("^rgba\\(" + [Et, Et, Et, Yt] + "\\)$"),
	         Xt = new RegExp("^rgba\\(" + [Pt, Pt, Pt, Yt] + "\\)$"),
	         It = new RegExp("^hsl\\(" + [Yt, Pt, Pt] + "\\)$"),
	         $t = new RegExp("^hsla\\(" + [Yt, Pt, Pt, Yt] + "\\)$"),
	         Ot = {
	             aliceblue: 15792383,
	             antiquewhite: 16444375,
	             aqua: 65535,
	             aquamarine: 8388564,
	             azure: 15794175,
	             beige: 16119260,
	             bisque: 16770244,
	             black: 0,
	             blanchedalmond: 16772045,
	             blue: 255,
	             blueviolet: 9055202,
	             brown: 10824234,
	             burlywood: 14596231,
	             cadetblue: 6266528,
	             chartreuse: 8388352,
	             chocolate: 13789470,
	             coral: 16744272,
	             cornflowerblue: 6591981,
	             cornsilk: 16775388,
	             crimson: 14423100,
	             cyan: 65535,
	             darkblue: 139,
	             darkcyan: 35723,
	             darkgoldenrod: 12092939,
	             darkgray: 11119017,
	             darkgreen: 25600,
	             darkgrey: 11119017,
	             darkkhaki: 12433259,
	             darkmagenta: 9109643,
	             darkolivegreen: 5597999,
	             darkorange: 16747520,
	             darkorchid: 10040012,
	             darkred: 9109504,
	             darksalmon: 15308410,
	             darkseagreen: 9419919,
	             darkslateblue: 4734347,
	             darkslategray: 3100495,
	             darkslategrey: 3100495,
	             darkturquoise: 52945,
	             darkviolet: 9699539,
	             deeppink: 16716947,
	             deepskyblue: 49151,
	             dimgray: 6908265,
	             dimgrey: 6908265,
	             dodgerblue: 2003199,
	             firebrick: 11674146,
	             floralwhite: 16775920,
	             forestgreen: 2263842,
	             fuchsia: 16711935,
	             gainsboro: 14474460,
	             ghostwhite: 16316671,
	             gold: 16766720,
	             goldenrod: 14329120,
	             gray: 8421504,
	             green: 32768,
	             greenyellow: 11403055,
	             grey: 8421504,
	             honeydew: 15794160,
	             hotpink: 16738740,
	             indianred: 13458524,
	             indigo: 4915330,
	             ivory: 16777200,
	             khaki: 15787660,
	             lavender: 15132410,
	             lavenderblush: 16773365,
	             lawngreen: 8190976,
	             lemonchiffon: 16775885,
	             lightblue: 11393254,
	             lightcoral: 15761536,
	             lightcyan: 14745599,
	             lightgoldenrodyellow: 16448210,
	             lightgray: 13882323,
	             lightgreen: 9498256,
	             lightgrey: 13882323,
	             lightpink: 16758465,
	             lightsalmon: 16752762,
	             lightseagreen: 2142890,
	             lightskyblue: 8900346,
	             lightslategray: 7833753,
	             lightslategrey: 7833753,
	             lightsteelblue: 11584734,
	             lightyellow: 16777184,
	             lime: 65280,
	             limegreen: 3329330,
	             linen: 16445670,
	             magenta: 16711935,
	             maroon: 8388608,
	             mediumaquamarine: 6737322,
	             mediumblue: 205,
	             mediumorchid: 12211667,
	             mediumpurple: 9662683,
	             mediumseagreen: 3978097,
	             mediumslateblue: 8087790,
	             mediumspringgreen: 64154,
	             mediumturquoise: 4772300,
	             mediumvioletred: 13047173,
	             midnightblue: 1644912,
	             mintcream: 16121850,
	             mistyrose: 16770273,
	             moccasin: 16770229,
	             navajowhite: 16768685,
	             navy: 128,
	             oldlace: 16643558,
	             olive: 8421376,
	             olivedrab: 7048739,
	             orange: 16753920,
	             orangered: 16729344,
	             orchid: 14315734,
	             palegoldenrod: 15657130,
	             palegreen: 10025880,
	             paleturquoise: 11529966,
	             palevioletred: 14381203,
	             papayawhip: 16773077,
	             peachpuff: 16767673,
	             peru: 13468991,
	             pink: 16761035,
	             plum: 14524637,
	             powderblue: 11591910,
	             purple: 8388736,
	             rebeccapurple: 6697881,
	             red: 16711680,
	             rosybrown: 12357519,
	             royalblue: 4286945,
	             saddlebrown: 9127187,
	             salmon: 16416882,
	             sandybrown: 16032864,
	             seagreen: 3050327,
	             seashell: 16774638,
	             sienna: 10506797,
	             silver: 12632256,
	             skyblue: 8900331,
	             slateblue: 6970061,
	             slategray: 7372944,
	             slategrey: 7372944,
	             snow: 16775930,
	             springgreen: 65407,
	             steelblue: 4620980,
	             tan: 13808780,
	             teal: 32896,
	             thistle: 14204888,
	             tomato: 16737095,
	             turquoise: 4251856,
	             violet: 15631086,
	             wheat: 16113331,
	             white: 16777215,
	             whitesmoke: 16119285,
	             yellow: 16776960,
	             yellowgreen: 10145074
	         };

	     function qt(t) {
	         var n;
	         return t = (t + "").trim().toLowerCase(), (n = Ht.exec(t)) ? new Qt((n = parseInt(n[1], 16)) >> 8 & 15 | n >> 4 & 240, n >> 4 & 15 | 240 & n, (15 & n) << 4 | 15 & n, 1) : (n = Ft.exec(t)) ? Vt(parseInt(n[1], 16)) : (n = jt.exec(t)) ? new Qt(n[1], n[2], n[3], 1) : (n = Bt.exec(t)) ? new Qt(255 * n[1] / 100, 255 * n[2] / 100, 255 * n[3] / 100, 1) : (n = Lt.exec(t)) ? Rt(n[1], n[2], n[3], n[4]) : (n = Xt.exec(t)) ? Rt(255 * n[1] / 100, 255 * n[2] / 100, 255 * n[3] / 100, n[4]) : (n = It.exec(t)) ? Gt(n[1], n[2] / 100, n[3] / 100, 1) : (n = $t.exec(t)) ? Gt(n[1], n[2] / 100, n[3] / 100, n[4]) : Ot.hasOwnProperty(t) ? Vt(Ot[t]) : "transparent" === t ? new Qt(NaN, NaN, NaN, 0) : null
	     }

	     function Vt(t) {
	         return new Qt(t >> 16 & 255, t >> 8 & 255, 255 & t, 1)
	     }

	     function Rt(t, n, e, r) {
	         return r <= 0 && (t = n = e = NaN), new Qt(t, n, e, r)
	     }

	     function Wt(t) {
	         return t instanceof Dt || (t = qt(t)), t ? new Qt((t = t.rgb()).r, t.g, t.b, t.opacity) : new Qt
	     }

	     function Zt(t, n, e, r) {
	         return 1 === arguments.length ? Wt(t) : new Qt(t, n, e, null == r ? 1 : r)
	     }

	     function Qt(t, n, e, r) {
	         this.r = +t, this.g = +n, this.b = +e, this.opacity = +r
	     }

	     function Jt(t) {
	         return ((t = Math.max(0, Math.min(255, Math.round(t) || 0))) < 16 ? "0" : "") + t.toString(16)
	     }

	     function Gt(t, n, e, r) {
	         return r <= 0 ? t = n = e = NaN : e <= 0 || e >= 1 ? t = n = NaN : n <= 0 && (t = NaN), new Kt(t, n, e, r)
	     }

	     function Kt(t, n, e, r) {
	         this.h = +t, this.s = +n, this.l = +e, this.opacity = +r
	     }

	     function tn(t, n, e) {
	         return 255 * (t < 60 ? n + (e - n) * t / 60 : t < 180 ? e : t < 240 ? n + (e - n) * (240 - t) / 60 : n)
	     }
	     St(Dt, qt, {
	         displayable: function() {
	             return this.rgb().displayable()
	         },
	         hex: function() {
	             return this.rgb().hex()
	         },
	         toString: function() {
	             return this.rgb() + ""
	         }
	     }), St(Qt, Zt, Ut(Dt, {
	         brighter: function(t) {
	             return t = null == t ? 1 / .7 : Math.pow(1 / .7, t), new Qt(this.r * t, this.g * t, this.b * t, this.opacity)
	         },
	         darker: function(t) {
	             return t = null == t ? .7 : Math.pow(.7, t), new Qt(this.r * t, this.g * t, this.b * t, this.opacity)
	         },
	         rgb: function() {
	             return this
	         },
	         displayable: function() {
	             return 0 <= this.r && this.r <= 255 && 0 <= this.g && this.g <= 255 && 0 <= this.b && this.b <= 255 && 0 <= this.opacity && this.opacity <= 1
	         },
	         hex: function() {
	             return "#" + Jt(this.r) + Jt(this.g) + Jt(this.b)
	         },
	         toString: function() {
	             var t = this.opacity;
	             return (1 === (t = isNaN(t) ? 1 : Math.max(0, Math.min(1, t))) ? "rgb(" : "rgba(") + Math.max(0, Math.min(255, Math.round(this.r) || 0)) + ", " + Math.max(0, Math.min(255, Math.round(this.g) || 0)) + ", " + Math.max(0, Math.min(255, Math.round(this.b) || 0)) + (1 === t ? ")" : ", " + t + ")")
	         }
	     })), St(Kt, function(t, n, e, r) {
	         return 1 === arguments.length ? function(t) {
	             if (t instanceof Kt) return new Kt(t.h, t.s, t.l, t.opacity);
	             if (t instanceof Dt || (t = qt(t)), !t) return new Kt;
	             if (t instanceof Kt) return t;
	             var n = (t = t.rgb()).r / 255,
	                 e = t.g / 255,
	                 r = t.b / 255,
	                 i = Math.min(n, e, r),
	                 o = Math.max(n, e, r),
	                 u = NaN,
	                 a = o - i,
	                 s = (o + i) / 2;
	             return a ? (u = n === o ? (e - r) / a + 6 * (e < r) : e === o ? (r - n) / a + 2 : (n - e) / a + 4, a /= s < .5 ? o + i : 2 - o - i, u *= 60) : a = s > 0 && s < 1 ? 0 : u, new Kt(u, a, s, t.opacity)
	         }(t) : new Kt(t, n, e, null == r ? 1 : r)
	     }, Ut(Dt, {
	         brighter: function(t) {
	             return t = null == t ? 1 / .7 : Math.pow(1 / .7, t), new Kt(this.h, this.s, this.l * t, this.opacity)
	         },
	         darker: function(t) {
	             return t = null == t ? .7 : Math.pow(.7, t), new Kt(this.h, this.s, this.l * t, this.opacity)
	         },
	         rgb: function() {
	             var t = this.h % 360 + 360 * (this.h < 0),
	                 n = isNaN(t) || isNaN(this.s) ? 0 : this.s,
	                 e = this.l,
	                 r = e + (e < .5 ? e : 1 - e) * n,
	                 i = 2 * e - r;
	             return new Qt(tn(t >= 240 ? t - 240 : t + 120, i, r), tn(t, i, r), tn(t < 120 ? t + 240 : t - 120, i, r), this.opacity)
	         },
	         displayable: function() {
	             return (0 <= this.s && this.s <= 1 || isNaN(this.s)) && 0 <= this.l && this.l <= 1 && 0 <= this.opacity && this.opacity <= 1
	         }
	     }));
	     var nn = Math.PI / 180,
	         en = 180 / Math.PI,
	         rn = .96422,
	         on = 1,
	         un = .82521,
	         an = 4 / 29,
	         sn = 6 / 29,
	         cn = 3 * sn * sn,
	         hn = sn * sn * sn;

	     function ln(t) {
	         if (t instanceof fn) return new fn(t.l, t.a, t.b, t.opacity);
	         if (t instanceof mn) {
	             if (isNaN(t.h)) return new fn(t.l, 0, 0, t.opacity);
	             var n = t.h * nn;
	             return new fn(t.l, Math.cos(n) * t.c, Math.sin(n) * t.c, t.opacity)
	         }
	         t instanceof Qt || (t = Wt(t));
	         var e, r, i = vn(t.r),
	             o = vn(t.g),
	             u = vn(t.b),
	             a = pn((.2225045 * i + .7168786 * o + .0606169 * u) / on);
	         return i === o && o === u ? e = r = a : (e = pn((.4360747 * i + .3850649 * o + .1430804 * u) / rn), r = pn((.0139322 * i + .0971045 * o + .7141733 * u) / un)), new fn(116 * a - 16, 500 * (e - a), 200 * (a - r), t.opacity)
	     }

	     function fn(t, n, e, r) {
	         this.l = +t, this.a = +n, this.b = +e, this.opacity = +r
	     }

	     function pn(t) {
	         return t > hn ? Math.pow(t, 1 / 3) : t / cn + an
	     }

	     function dn(t) {
	         return t > sn ? t * t * t : cn * (t - an)
	     }

	     function gn(t) {
	         return 255 * (t <= .0031308 ? 12.92 * t : 1.055 * Math.pow(t, 1 / 2.4) - .055)
	     }

	     function vn(t) {
	         return (t /= 255) <= .04045 ? t / 12.92 : Math.pow((t + .055) / 1.055, 2.4)
	     }

	     function mn(t, n, e, r) {
	         this.h = +t, this.c = +n, this.l = +e, this.opacity = +r
	     }
	     St(fn, function(t, n, e, r) {
	         return 1 === arguments.length ? ln(t) : new fn(t, n, e, null == r ? 1 : r)
	     }, Ut(Dt, {
	         brighter: function(t) {
	             return new fn(this.l + 18 * (null == t ? 1 : t), this.a, this.b, this.opacity)
	         },
	         darker: function(t) {
	             return new fn(this.l - 18 * (null == t ? 1 : t), this.a, this.b, this.opacity)
	         },
	         rgb: function() {
	             var t = (this.l + 16) / 116,
	                 n = isNaN(this.a) ? t : t + this.a / 500,
	                 e = isNaN(this.b) ? t : t - this.b / 200;
	             return new Qt(gn(3.1338561 * (n = rn * dn(n)) - 1.6168667 * (t = on * dn(t)) - .4906146 * (e = un * dn(e))), gn(-.9787684 * n + 1.9161415 * t + .033454 * e), gn(.0719453 * n - .2289914 * t + 1.4052427 * e), this.opacity)
	         }
	     })), St(mn, function(t, n, e, r) {
	         return 1 === arguments.length ? function(t) {
	             if (t instanceof mn) return new mn(t.h, t.c, t.l, t.opacity);
	             if (t instanceof fn || (t = ln(t)), 0 === t.a && 0 === t.b) return new mn(NaN, 0, t.l, t.opacity);
	             var n = Math.atan2(t.b, t.a) * en;
	             return new mn(n < 0 ? n + 360 : n, Math.sqrt(t.a * t.a + t.b * t.b), t.l, t.opacity)
	         }(t) : new mn(t, n, e, null == r ? 1 : r)
	     }, Ut(Dt, {
	         brighter: function(t) {
	             return new mn(this.h, this.c, this.l + 18 * (null == t ? 1 : t), this.opacity)
	         },
	         darker: function(t) {
	             return new mn(this.h, this.c, this.l - 18 * (null == t ? 1 : t), this.opacity)
	         },
	         rgb: function() {
	             return ln(this).rgb()
	         }
	     }));
	     var yn = -.14861,
	         _n = 1.78277,
	         wn = -.29227,
	         xn = -.90649,
	         bn = 1.97294,
	         Mn = bn * xn,
	         Tn = bn * _n,
	         Cn = _n * wn - xn * yn;

	     function kn(t, n, e, r) {
	         this.h = +t, this.s = +n, this.l = +e, this.opacity = +r
	     }

	     function An(t) {
	         return function() {
	             return t
	         }
	     }

	     function zn(t) {
	         return 1 == (t = +t) ? Nn : function(n, e) {
	             return e - n ? function(t, n, e) {
	                 return t = Math.pow(t, e), n = Math.pow(n, e) - t, e = 1 / e,
	                     function(r) {
	                         return Math.pow(t + r * n, e)
	                     }
	             }(n, e, t) : An(isNaN(n) ? e : n)
	         }
	     }

	     function Nn(t, n) {
	         var e = n - t;
	         return e ? function(t, n) {
	             return function(e) {
	                 return t + e * n
	             }
	         }(t, e) : An(isNaN(t) ? n : t)
	     }
	     St(kn, function(t, n, e, r) {
	         return 1 === arguments.length ? function(t) {
	             if (t instanceof kn) return new kn(t.h, t.s, t.l, t.opacity);
	             t instanceof Qt || (t = Wt(t));
	             var n = t.r / 255,
	                 e = t.g / 255,
	                 r = t.b / 255,
	                 i = (Cn * r + Mn * n - Tn * e) / (Cn + Mn - Tn),
	                 o = r - i,
	                 u = (bn * (e - i) - wn * o) / xn,
	                 a = Math.sqrt(u * u + o * o) / (bn * i * (1 - i)),
	                 s = a ? Math.atan2(u, o) * en - 120 : NaN;
	             return new kn(s < 0 ? s + 360 : s, a, i, t.opacity)
	         }(t) : new kn(t, n, e, null == r ? 1 : r)
	     }, Ut(Dt, {
	         brighter: function(t) {
	             return t = null == t ? 1 / .7 : Math.pow(1 / .7, t), new kn(this.h, this.s, this.l * t, this.opacity)
	         },
	         darker: function(t) {
	             return t = null == t ? .7 : Math.pow(.7, t), new kn(this.h, this.s, this.l * t, this.opacity)
	         },
	         rgb: function() {
	             var t = isNaN(this.h) ? 0 : (this.h + 120) * nn,
	                 n = +this.l,
	                 e = isNaN(this.s) ? 0 : this.s * n * (1 - n),
	                 r = Math.cos(t),
	                 i = Math.sin(t);
	             return new Qt(255 * (n + e * (yn * r + _n * i)), 255 * (n + e * (wn * r + xn * i)), 255 * (n + e * (bn * r)), this.opacity)
	         }
	     }));
	     var Sn = function t(n) {
	         var e = zn(n);

	         function r(t, n) {
	             var r = e((t = Zt(t)).r, (n = Zt(n)).r),
	                 i = e(t.g, n.g),
	                 o = e(t.b, n.b),
	                 u = Nn(t.opacity, n.opacity);
	             return function(n) {
	                 return t.r = r(n), t.g = i(n), t.b = o(n), t.opacity = u(n), t + ""
	             }
	         }
	         return r.gamma = t, r
	     }(1);

	     function Un(t, n) {
	         return n -= t = +t,
	             function(e) {
	                 return t + n * e
	             }
	     }
	     var Dn = /[-+]?(?:\d+\.?\d*|\.?\d+)(?:[eE][-+]?\d+)?/g,
	         En = new RegExp(Dn.source, "g");
	     var Yn, Pn, Hn, Fn, jn = 180 / Math.PI,
	         Bn = {
	             translateX: 0,
	             translateY: 0,
	             rotate: 0,
	             skewX: 0,
	             scaleX: 1,
	             scaleY: 1
	         };

	     function Ln(t, n, e, r, i, o) {
	         var u, a, s;
	         return (u = Math.sqrt(t * t + n * n)) && (t /= u, n /= u), (s = t * e + n * r) && (e -= t * s, r -= n * s), (a = Math.sqrt(e * e + r * r)) && (e /= a, r /= a, s /= a), t * r < n * e && (t = -t, n = -n, s = -s, u = -u), {
	             translateX: i,
	             translateY: o,
	             rotate: Math.atan2(n, t) * jn,
	             skewX: Math.atan(s) * jn,
	             scaleX: u,
	             scaleY: a
	         }
	     }

	     function Xn(t, n, e, r) {
	         function i(t) {
	             return t.length ? t.pop() + " " : ""
	         }
	         return function(o, u) {
	             var a = [],
	                 s = [];
	             return o = t(o), u = t(u),
	                 function(t, r, i, o, u, a) {
	                     if (t !== i || r !== o) {
	                         var s = u.push("translate(", null, n, null, e);
	                         a.push({
	                             i: s - 4,
	                             x: Un(t, i)
	                         }, {
	                             i: s - 2,
	                             x: Un(r, o)
	                         })
	                     } else(i || o) && u.push("translate(" + i + n + o + e)
	                 }(o.translateX, o.translateY, u.translateX, u.translateY, a, s),
	                 function(t, n, e, o) {
	                     t !== n ? (t - n > 180 ? n += 360 : n - t > 180 && (t += 360), o.push({
	                         i: e.push(i(e) + "rotate(", null, r) - 2,
	                         x: Un(t, n)
	                     })) : n && e.push(i(e) + "rotate(" + n + r)
	                 }(o.rotate, u.rotate, a, s),
	                 function(t, n, e, o) {
	                     t !== n ? o.push({
	                         i: e.push(i(e) + "skewX(", null, r) - 2,
	                         x: Un(t, n)
	                     }) : n && e.push(i(e) + "skewX(" + n + r)
	                 }(o.skewX, u.skewX, a, s),
	                 function(t, n, e, r, o, u) {
	                     if (t !== e || n !== r) {
	                         var a = o.push(i(o) + "scale(", null, ",", null, ")");
	                         u.push({
	                             i: a - 4,
	                             x: Un(t, e)
	                         }, {
	                             i: a - 2,
	                             x: Un(n, r)
	                         })
	                     } else 1 === e && 1 === r || o.push(i(o) + "scale(" + e + "," + r + ")")
	                 }(o.scaleX, o.scaleY, u.scaleX, u.scaleY, a, s), o = u = null,
	                 function(t) {
	                     for (var n, e = -1, r = s.length; ++e < r;) a[(n = s[e]).i] = n.x(t);
	                     return a.join("")
	                 }
	         }
	     }
	     var In = Xn(function(t) {
	             return "none" === t ? Bn : (Yn || (Yn = document.createElement("DIV"), Pn = document.documentElement, Hn = document.defaultView), Yn.style.transform = t, t = Hn.getComputedStyle(Pn.appendChild(Yn), null).getPropertyValue("transform"), Pn.removeChild(Yn), Ln(+(t = t.slice(7, -1).split(","))[0], +t[1], +t[2], +t[3], +t[4], +t[5]))
	         }, "px, ", "px)", "deg)"),
	         $n = Xn(function(t) {
	             return null == t ? Bn : (Fn || (Fn = document.createElementNS("http://www.w3.org/2000/svg", "g")), Fn.setAttribute("transform", t), (t = Fn.transform.baseVal.consolidate()) ? Ln((t = t.matrix).a, t.b, t.c, t.d, t.e, t.f) : Bn)
	         }, ", ", ")", ")"),
	         On = Math.SQRT2,
	         qn = 2,
	         Vn = 4,
	         Rn = 1e-12;

	     function Wn(t) {
	         return ((t = Math.exp(t)) + 1 / t) / 2
	     }

	     function Zn(t, n) {
	         var e, r, i = t[0],
	             o = t[1],
	             u = t[2],
	             a = n[0],
	             s = n[1],
	             c = n[2],
	             h = a - i,
	             l = s - o,
	             f = h * h + l * l;
	         if (f < Rn) r = Math.log(c / u) / On, e = function(t) {
	             return [i + t * h, o + t * l, u * Math.exp(On * t * r)]
	         };
	         else {
	             var p = Math.sqrt(f),
	                 d = (c * c - u * u + Vn * f) / (2 * u * qn * p),
	                 g = (c * c - u * u - Vn * f) / (2 * c * qn * p),
	                 v = Math.log(Math.sqrt(d * d + 1) - d),
	                 m = Math.log(Math.sqrt(g * g + 1) - g);
	             r = (m - v) / On, e = function(t) {
	                 var n, e = t * r,
	                     a = Wn(v),
	                     s = u / (qn * p) * (a * (n = On * e + v, ((n = Math.exp(2 * n)) - 1) / (n + 1)) - function(t) {
	                         return ((t = Math.exp(t)) - 1 / t) / 2
	                     }(v));
	                 return [i + s * h, o + s * l, u * a / Wn(On * e + v)]
	             }
	         }
	         return e.duration = 1e3 * r, e
	     }

	     function Qn(t, n, e) {
	         var r = t._id;
	         return t.each(function() {
	                 var t = At(this, r);
	                 (t.value || (t.value = {}))[n] = e.apply(this, arguments)
	             }),
	             function(t) {
	                 return zt(t, r).value[n]
	             }
	     }

	     function Jn(t, n) {
	         var e;
	         return ("number" == typeof n ? Un : n instanceof qt ? Sn : (e = qt(n)) ? (n = e, Sn) : function(t, n) {
	             var e, r, i, o = Dn.lastIndex = En.lastIndex = 0,
	                 u = -1,
	                 a = [],
	                 s = [];
	             for (t += "", n += "";
	                 (e = Dn.exec(t)) && (r = En.exec(n));)(i = r.index) > o && (i = n.slice(o, i), a[u] ? a[u] += i : a[++u] = i), (e = e[0]) === (r = r[0]) ? a[u] ? a[u] += r : a[++u] = r : (a[++u] = null, s.push({
	                 i: u,
	                 x: Un(e, r)
	             })), o = En.lastIndex;
	             return o < n.length && (i = n.slice(o), a[u] ? a[u] += i : a[++u] = i), a.length < 2 ? s[0] ? function(t) {
	                 return function(n) {
	                     return t(n) + ""
	                 }
	             }(s[0].x) : function(t) {
	                 return function() {
	                     return t
	                 }
	             }(n) : (n = s.length, function(t) {
	                 for (var e, r = 0; r < n; ++r) a[(e = s[r]).i] = e.x(t);
	                 return a.join("")
	             })
	         })(t, n)
	     }
	     var Gn = L.prototype.constructor;

	     function Kn(t) {
	         return function() {
	             this.style.removeProperty(t)
	         }
	     }
	     var te = 0;

	     function ne(t, n, e, r) {
	         this._groups = t, this._parents = n, this._name = e, this._id = r
	     }

	     function ee() {
	         return ++te
	     }
	     var re = L.prototype;
	     ne.prototype = function(t) {
	         return L().transition(t)
	     }.prototype = {
	         constructor: ne,
	         select: function(t) {
	             var n = this._name,
	                 e = this._id;
	             "function" != typeof t && (t = u(t));
	             for (var r = this._groups, i = r.length, o = new Array(i), a = 0; a < i; ++a)
	                 for (var s, c, h = r[a], l = h.length, f = o[a] = new Array(l), p = 0; p < l; ++p)(s = h[p]) && (c = t.call(s, s.__data__, p, h)) && ("__data__" in s && (c.__data__ = s.__data__), f[p] = c, Ct(f[p], n, e, p, f, zt(s, e)));
	             return new ne(o, this._parents, n, e)
	         },
	         selectAll: function(t) {
	             var n = this._name,
	                 e = this._id;
	             "function" != typeof t && (t = s(t));
	             for (var r = this._groups, i = r.length, o = [], u = [], a = 0; a < i; ++a)
	                 for (var c, h = r[a], l = h.length, f = 0; f < l; ++f)
	                     if (c = h[f]) {
	                         for (var p, d = t.call(c, c.__data__, f, h), g = zt(c, e), v = 0, m = d.length; v < m; ++v)(p = d[v]) && Ct(p, n, e, v, d, g);
	                         o.push(d), u.push(c)
	                     }
	             return new ne(o, u, n, e)
	         },
	         filter: function(t) {
	             "function" != typeof t && (t = c(t));
	             for (var n = this._groups, e = n.length, r = new Array(e), i = 0; i < e; ++i)
	                 for (var o, u = n[i], a = u.length, s = r[i] = [], h = 0; h < a; ++h)(o = u[h]) && t.call(o, o.__data__, h, u) && s.push(o);
	             return new ne(r, this._parents, this._name, this._id)
	         },
	         merge: function(t) {
	             if (t._id !== this._id) throw new Error;
	             for (var n = this._groups, e = t._groups, r = n.length, i = e.length, o = Math.min(r, i), u = new Array(r), a = 0; a < o; ++a)
	                 for (var s, c = n[a], h = e[a], l = c.length, f = u[a] = new Array(l), p = 0; p < l; ++p)(s = c[p] || h[p]) && (f[p] = s);
	             for (; a < r; ++a) u[a] = n[a];
	             return new ne(u, this._parents, this._name, this._id)
	         },
	         selection: function() {
	             return new Gn(this._groups, this._parents)
	         },
	         transition: function() {
	             for (var t = this._name, n = this._id, e = ee(), r = this._groups, i = r.length, o = 0; o < i; ++o)
	                 for (var u, a = r[o], s = a.length, c = 0; c < s; ++c)
	                     if (u = a[c]) {
	                         var h = zt(u, n);
	                         Ct(u, t, e, c, a, {
	                             time: h.time + h.delay + h.duration,
	                             delay: 0,
	                             duration: h.duration,
	                             ease: h.ease
	                         })
	                     }
	             return new ne(r, this._parents, t, e)
	         },
	         call: re.call,
	         nodes: re.nodes,
	         node: re.node,
	         size: re.size,
	         empty: re.empty,
	         each: re.each,
	         on: function(t, n) {
	             var e = this._id;
	             return arguments.length < 2 ? zt(this.node(), e).on.on(t) : this.each(function(t, n, e) {
	                 var r, i, o = function(t) {
	                     return (t + "").trim().split(/^|\s+/).every(function(t) {
	                         var n = t.indexOf(".");
	                         return n >= 0 && (t = t.slice(0, n)), !t || "start" === t
	                     })
	                 }(n) ? kt : At;
	                 return function() {
	                     var u = o(this, t),
	                         a = u.on;
	                     a !== r && (i = (r = a).copy()).on(n, e), u.on = i
	                 }
	             }(e, t, n))
	         },
	         attr: function(t, n) {
	             var e = r(t),
	                 i = "transform" === e ? $n : Jn;
	             return this.attrTween(t, "function" == typeof n ? (e.local ? function(t, n, e) {
	                 var r, i, o;
	                 return function() {
	                     var u, a, s = e(this);
	                     if (null != s) return (u = this.getAttributeNS(t.space, t.local)) === (a = s + "") ? null : u === r && a === i ? o : (i = a, o = n(r = u, s));
	                     this.removeAttributeNS(t.space, t.local)
	                 }
	             } : function(t, n, e) {
	                 var r, i, o;
	                 return function() {
	                     var u, a, s = e(this);
	                     if (null != s) return (u = this.getAttribute(t)) === (a = s + "") ? null : u === r && a === i ? o : (i = a, o = n(r = u, s));
	                     this.removeAttribute(t)
	                 }
	             })(e, i, Qn(this, "attr." + t, n)) : null == n ? (e.local ? function(t) {
	                 return function() {
	                     this.removeAttributeNS(t.space, t.local)
	                 }
	             } : function(t) {
	                 return function() {
	                     this.removeAttribute(t)
	                 }
	             })(e) : (e.local ? function(t, n, e) {
	                 var r, i, o = e + "";
	                 return function() {
	                     var u = this.getAttributeNS(t.space, t.local);
	                     return u === o ? null : u === r ? i : i = n(r = u, e)
	                 }
	             } : function(t, n, e) {
	                 var r, i, o = e + "";
	                 return function() {
	                     var u = this.getAttribute(t);
	                     return u === o ? null : u === r ? i : i = n(r = u, e)
	                 }
	             })(e, i, n))
	         },
	         attrTween: function(t, n) {
	             var e = "attr." + t;
	             if (arguments.length < 2) return (e = this.tween(e)) && e._value;
	             if (null == n) return this.tween(e, null);
	             if ("function" != typeof n) throw new Error;
	             var i = r(t);
	             return this.tween(e, (i.local ? function(t, n) {
	                 var e, r;

	                 function i() {
	                     var i = n.apply(this, arguments);
	                     return i !== r && (e = (r = i) && function(t, n) {
	                         return function(e) {
	                             this.setAttributeNS(t.space, t.local, n(e))
	                         }
	                     }(t, i)), e
	                 }
	                 return i._value = n, i
	             } : function(t, n) {
	                 var e, r;

	                 function i() {
	                     var i = n.apply(this, arguments);
	                     return i !== r && (e = (r = i) && function(t, n) {
	                         return function(e) {
	                             this.setAttribute(t, n(e))
	                         }
	                     }(t, i)), e
	                 }
	                 return i._value = n, i
	             })(i, n))
	         },
	         style: function(t, n, e) {
	             var r = "transform" == (t += "") ? In : Jn;
	             return null == n ? this.styleTween(t, function(t, n) {
	                 var e, r, i;
	                 return function() {
	                     var o = m(this, t),
	                         u = (this.style.removeProperty(t), m(this, t));
	                     return o === u ? null : o === e && u === r ? i : i = n(e = o, r = u)
	                 }
	             }(t, r)).on("end.style." + t, Kn(t)) : "function" == typeof n ? this.styleTween(t, function(t, n, e) {
	                 var r, i, o;
	                 return function() {
	                     var u = m(this, t),
	                         a = e(this),
	                         s = a + "";
	                     return null == a && (this.style.removeProperty(t), s = a = m(this, t)), u === s ? null : u === r && s === i ? o : (i = s, o = n(r = u, a))
	                 }
	             }(t, r, Qn(this, "style." + t, n))).each(function(t, n) {
	                 var e, r, i, o, u = "style." + n,
	                     a = "end." + u;
	                 return function() {
	                     var s = At(this, t),
	                         c = s.on,
	                         h = null == s.value[u] ? o || (o = Kn(n)) : void 0;
	                     c === e && i === h || (r = (e = c).copy()).on(a, i = h), s.on = r
	                 }
	             }(this._id, t)) : this.styleTween(t, function(t, n, e) {
	                 var r, i, o = e + "";
	                 return function() {
	                     var u = m(this, t);
	                     return u === o ? null : u === r ? i : i = n(r = u, e)
	                 }
	             }(t, r, n), e).on("end.style." + t, null)
	         },
	         styleTween: function(t, n, e) {
	             var r = "style." + (t += "");
	             if (arguments.length < 2) return (r = this.tween(r)) && r._value;
	             if (null == n) return this.tween(r, null);
	             if ("function" != typeof n) throw new Error;
	             return this.tween(r, function(t, n, e) {
	                 var r, i;

	                 function o() {
	                     var o = n.apply(this, arguments);
	                     return o !== i && (r = (i = o) && function(t, n, e) {
	                         return function(r) {
	                             this.style.setProperty(t, n(r), e)
	                         }
	                     }(t, o, e)), r
	                 }
	                 return o._value = n, o
	             }(t, n, null == e ? "" : e))
	         },
	         text: function(t) {
	             return this.tween("text", "function" == typeof t ? function(t) {
	                 return function() {
	                     var n = t(this);
	                     this.textContent = null == n ? "" : n
	                 }
	             }(Qn(this, "text", t)) : function(t) {
	                 return function() {
	                     this.textContent = t
	                 }
	             }(null == t ? "" : t + ""))
	         },
	         remove: function() {
	             return this.on("end.remove", (t = this._id, function() {
	                 var n = this.parentNode;
	                 for (var e in this.__transition)
	                     if (+e !== t) return;
	                 n && n.removeChild(this)
	             }));
	             var t
	         },
	         tween: function(t, n) {
	             var e = this._id;
	             if (t += "", arguments.length < 2) {
	                 for (var r, i = zt(this.node(), e).tween, o = 0, u = i.length; o < u; ++o)
	                     if ((r = i[o]).name === t) return r.value;
	                 return null
	             }
	             return this.each((null == n ? function(t, n) {
	                 var e, r;
	                 return function() {
	                     var i = At(this, t),
	                         o = i.tween;
	                     if (o !== e)
	                         for (var u = 0, a = (r = e = o).length; u < a; ++u)
	                             if (r[u].name === n) {
	                                 (r = r.slice()).splice(u, 1);
	                                 break
	                             }
	                     i.tween = r
	                 }
	             } : function(t, n, e) {
	                 var r, i;
	                 if ("function" != typeof e) throw new Error;
	                 return function() {
	                     var o = At(this, t),
	                         u = o.tween;
	                     if (u !== r) {
	                         i = (r = u).slice();
	                         for (var a = {
	                                 name: n,
	                                 value: e
	                             }, s = 0, c = i.length; s < c; ++s)
	                             if (i[s].name === n) {
	                                 i[s] = a;
	                                 break
	                             }
	                         s === c && i.push(a)
	                     }
	                     o.tween = i
	                 }
	             })(e, t, n))
	         },
	         delay: function(t) {
	             var n = this._id;
	             return arguments.length ? this.each(("function" == typeof t ? function(t, n) {
	                 return function() {
	                     kt(this, t).delay = +n.apply(this, arguments)
	                 }
	             } : function(t, n) {
	                 return n = +n,
	                     function() {
	                         kt(this, t).delay = n
	                     }
	             })(n, t)) : zt(this.node(), n).delay
	         },
	         duration: function(t) {
	             var n = this._id;
	             return arguments.length ? this.each(("function" == typeof t ? function(t, n) {
	                 return function() {
	                     At(this, t).duration = +n.apply(this, arguments)
	                 }
	             } : function(t, n) {
	                 return n = +n,
	                     function() {
	                         At(this, t).duration = n
	                     }
	             })(n, t)) : zt(this.node(), n).duration
	         },
	         ease: function(t) {
	             var n = this._id;
	             return arguments.length ? this.each(function(t, n) {
	                 if ("function" != typeof n) throw new Error;
	                 return function() {
	                     At(this, t).ease = n
	                 }
	             }(n, t)) : zt(this.node(), n).ease
	         },
	         end: function() {
	             var t, n, e = this,
	                 r = e._id,
	                 i = e.size();
	             return new Promise(function(o, u) {
	                 var a = {
	                         value: u
	                     },
	                     s = {
	                         value: function() {
	                             0 == --i && o()
	                         }
	                     };
	                 e.each(function() {
	                     var e = At(this, r),
	                         i = e.on;
	                     i !== t && ((n = (t = i).copy())._.cancel.push(a), n._.interrupt.push(a), n._.end.push(s)), e.on = n
	                 })
	             })
	         }
	     };
	     Math.PI, Math.PI;
	     var ie = {
	         time: null,
	         delay: 0,
	         duration: 250,
	         ease: function(t) {
	             return ((t *= 2) <= 1 ? t * t * t : (t -= 2) * t * t + 2) / 2
	         }
	     };

	     function oe(t, n) {
	         for (var e; !(e = t.__transition) || !(e = e[n]);)
	             if (!(t = t.parentNode)) return ie.time = st(), ie;
	         return e
	     }

	     function ue(t, n) {
	         return t < n ? -1 : t > n ? 1 : t >= n ? 0 : NaN
	     }
	     L.prototype.interrupt = function(t) {
	         return this.each(function() {
	             Nt(this, t)
	         })
	     }, L.prototype.transition = function(t) {
	         var n, e;
	         t instanceof ne ? (n = t._id, t = t._name) : (n = ee(), (e = ie).time = st(), t = null == t ? null : t + "");
	         for (var r = this._groups, i = r.length, o = 0; o < i; ++o)
	             for (var u, a = r[o], s = a.length, c = 0; c < s; ++c)(u = a[c]) && Ct(u, t, n, c, a, e || oe(u, n));
	         return new ne(r, this._parents, t, n)
	     };
	     var ae, se;
	     1 === (ae = ue).length && (se = ae, ae = function(t, n) {
	         return ue(se(t), n)
	     });

	     function ce() {}

	     function he(t, n) {
	         var e = new ce;
	         if (t instanceof ce) t.each(function(t, n) {
	             e.set(n, t)
	         });
	         else if (Array.isArray(t)) {
	             var r, i = -1,
	                 o = t.length;
	             if (null == n)
	                 for (; ++i < o;) e.set(i, t[i]);
	             else
	                 for (; ++i < o;) e.set(n(r = t[i], i, t), r)
	         } else if (t)
	             for (var u in t) e.set(u, t[u]);
	         return e
	     }

	     function le() {}
	     ce.prototype = he.prototype = {
	         constructor: ce,
	         has: function(t) {
	             return "$" + t in this
	         },
	         get: function(t) {
	             return this["$" + t]
	         },
	         set: function(t, n) {
	             return this["$" + t] = n, this
	         },
	         remove: function(t) {
	             var n = "$" + t;
	             return n in this && delete this[n]
	         },
	         clear: function() {
	             for (var t in this) "$" === t[0] && delete this[t]
	         },
	         keys: function() {
	             var t = [];
	             for (var n in this) "$" === n[0] && t.push(n.slice(1));
	             return t
	         },
	         values: function() {
	             var t = [];
	             for (var n in this) "$" === n[0] && t.push(this[n]);
	             return t
	         },
	         entries: function() {
	             var t = [];
	             for (var n in this) "$" === n[0] && t.push({
	                 key: n.slice(1),
	                 value: this[n]
	             });
	             return t
	         },
	         size: function() {
	             var t = 0;
	             for (var n in this) "$" === n[0] && ++t;
	             return t
	         },
	         empty: function() {
	             for (var t in this)
	                 if ("$" === t[0]) return !1;
	             return !0
	         },
	         each: function(t) {
	             for (var n in this) "$" === n[0] && t(this[n], n.slice(1), this)
	         }
	     };
	     var fe = he.prototype;

	     function pe(t, n) {
	         if ((e = (t = n ? t.toExponential(n - 1) : t.toExponential()).indexOf("e")) < 0) return null;
	         var e, r = t.slice(0, e);
	         return [r.length > 1 ? r[0] + r.slice(2) : r, +t.slice(e + 1)]
	     }
	     le.prototype = function(t, n) {
	         var e = new le;
	         if (t instanceof le) t.each(function(t) {
	             e.add(t)
	         });
	         else if (t) {
	             var r = -1,
	                 i = t.length;
	             if (null == n)
	                 for (; ++r < i;) e.add(t[r]);
	             else
	                 for (; ++r < i;) e.add(n(t[r], r, t))
	         }
	         return e
	     }.prototype = {
	         constructor: le,
	         has: fe.has,
	         add: function(t) {
	             return this["$" + (t += "")] = t, this
	         },
	         remove: fe.remove,
	         clear: fe.clear,
	         values: fe.keys,
	         size: fe.size,
	         empty: fe.empty,
	         each: fe.each
	     };
	     var de, ge = /^(?:(.)?([<>=^]))?([+\-( ])?([$#])?(0)?(\d+)?(,)?(\.\d+)?(~)?([a-z%])?$/i;

	     function ve(t) {
	         return new me(t)
	     }

	     function me(t) {
	         if (!(n = ge.exec(t))) throw new Error("invalid format: " + t);
	         var n;
	         this.fill = n[1] || " ", this.align = n[2] || ">", this.sign = n[3] || "-", this.symbol = n[4] || "", this.zero = !!n[5], this.width = n[6] && +n[6], this.comma = !!n[7], this.precision = n[8] && +n[8].slice(1), this.trim = !!n[9], this.type = n[10] || ""
	     }

	     function ye(t, n) {
	         var e = pe(t, n);
	         if (!e) return t + "";
	         var r = e[0],
	             i = e[1];
	         return i < 0 ? "0." + new Array(-i).join("0") + r : r.length > i + 1 ? r.slice(0, i + 1) + "." + r.slice(i + 1) : r + new Array(i - r.length + 2).join("0")
	     }
	     ve.prototype = me.prototype, me.prototype.toString = function() {
	         return this.fill + this.align + this.sign + this.symbol + (this.zero ? "0" : "") + (null == this.width ? "" : Math.max(1, 0 | this.width)) + (this.comma ? "," : "") + (null == this.precision ? "" : "." + Math.max(0, 0 | this.precision)) + (this.trim ? "~" : "") + this.type
	     };
	     var _e = {
	         "%": function(t, n) {
	             return (100 * t).toFixed(n)
	         },
	         b: function(t) {
	             return Math.round(t).toString(2)
	         },
	         c: function(t) {
	             return t + ""
	         },
	         d: function(t) {
	             return Math.round(t).toString(10)
	         },
	         e: function(t, n) {
	             return t.toExponential(n)
	         },
	         f: function(t, n) {
	             return t.toFixed(n)
	         },
	         g: function(t, n) {
	             return t.toPrecision(n)
	         },
	         o: function(t) {
	             return Math.round(t).toString(8)
	         },
	         p: function(t, n) {
	             return ye(100 * t, n)
	         },
	         r: ye,
	         s: function(t, n) {
	             var e = pe(t, n);
	             if (!e) return t + "";
	             var r = e[0],
	                 i = e[1],
	                 o = i - (de = 3 * Math.max(-8, Math.min(8, Math.floor(i / 3)))) + 1,
	                 u = r.length;
	             return o === u ? r : o > u ? r + new Array(o - u + 1).join("0") : o > 0 ? r.slice(0, o) + "." + r.slice(o) : "0." + new Array(1 - o).join("0") + pe(t, Math.max(0, n + o - 1))[0]
	         },
	         X: function(t) {
	             return Math.round(t).toString(16).toUpperCase()
	         },
	         x: function(t) {
	             return Math.round(t).toString(16)
	         }
	     };

	     function we(t) {
	         return t
	     }
	     var xe, be = ["y", "z", "a", "f", "p", "n", "µ", "m", "", "k", "M", "G", "T", "P", "E", "Z", "Y"];

	     function Me(t) {
	         var n, e, r = t.grouping && t.thousands ? (n = t.grouping, e = t.thousands, function(t, r) {
	                 for (var i = t.length, o = [], u = 0, a = n[0], s = 0; i > 0 && a > 0 && (s + a + 1 > r && (a = Math.max(1, r - s)), o.push(t.substring(i -= a, i + a)), !((s += a + 1) > r));) a = n[u = (u + 1) % n.length];
	                 return o.reverse().join(e)
	             }) : we,
	             i = t.currency,
	             o = t.decimal,
	             u = t.numerals ? function(t) {
	                 return function(n) {
	                     return n.replace(/[0-9]/g, function(n) {
	                         return t[+n]
	                     })
	                 }
	             }(t.numerals) : we,
	             a = t.percent || "%";

	         function s(t) {
	             var n = (t = ve(t)).fill,
	                 e = t.align,
	                 s = t.sign,
	                 c = t.symbol,
	                 h = t.zero,
	                 l = t.width,
	                 f = t.comma,
	                 p = t.precision,
	                 d = t.trim,
	                 g = t.type;
	             "n" === g ? (f = !0, g = "g") : _e[g] || (null == p && (p = 12), d = !0, g = "g"), (h || "0" === n && "=" === e) && (h = !0, n = "0", e = "=");
	             var v = "$" === c ? i[0] : "#" === c && /[boxX]/.test(g) ? "0" + g.toLowerCase() : "",
	                 m = "$" === c ? i[1] : /[%p]/.test(g) ? a : "",
	                 y = _e[g],
	                 _ = /[defgprs%]/.test(g);

	             function w(t) {
	                 var i, a, c, w = v,
	                     x = m;
	                 if ("c" === g) x = y(t) + x, t = "";
	                 else {
	                     var b = (t = +t) < 0;
	                     if (t = y(Math.abs(t), p), d && (t = function(t) {
	                             t: for (var n, e = t.length, r = 1, i = -1; r < e; ++r) switch (t[r]) {
	                                 case ".":
	                                     i = n = r;
	                                     break;
	                                 case "0":
	                                     0 === i && (i = r), n = r;
	                                     break;
	                                 default:
	                                     if (i > 0) {
	                                         if (!+t[r]) break t;
	                                         i = 0
	                                     }
	                             }
	                             return i > 0 ? t.slice(0, i) + t.slice(n + 1) : t
	                         }(t)), b && 0 == +t && (b = !1), w = (b ? "(" === s ? s : "-" : "-" === s || "(" === s ? "" : s) + w, x = ("s" === g ? be[8 + de / 3] : "") + x + (b && "(" === s ? ")" : ""), _)
	                         for (i = -1, a = t.length; ++i < a;)
	                             if (48 > (c = t.charCodeAt(i)) || c > 57) {
	                                 x = (46 === c ? o + t.slice(i + 1) : t.slice(i)) + x, t = t.slice(0, i);
	                                 break
	                             }
	                 }
	                 f && !h && (t = r(t, 1 / 0));
	                 var M = w.length + t.length + x.length,
	                     T = M < l ? new Array(l - M + 1).join(n) : "";
	                 switch (f && h && (t = r(T + t, T.length ? l - x.length : 1 / 0), T = ""), e) {
	                     case "<":
	                         t = w + t + x + T;
	                         break;
	                     case "=":
	                         t = w + T + t + x;
	                         break;
	                     case "^":
	                         t = T.slice(0, M = T.length >> 1) + w + t + x + T.slice(M);
	                         break;
	                     default:
	                         t = T + w + t + x
	                 }
	                 return u(t)
	             }
	             return p = null == p ? 6 : /[gprs]/.test(g) ? Math.max(1, Math.min(21, p)) : Math.max(0, Math.min(20, p)), w.toString = function() {
	                 return t + ""
	             }, w
	         }
	         return {
	             format: s,
	             formatPrefix: function(t, n) {
	                 var e, r = s(((t = ve(t)).type = "f", t)),
	                     i = 3 * Math.max(-8, Math.min(8, Math.floor((e = n, ((e = pe(Math.abs(e))) ? e[1] : NaN) / 3)))),
	                     o = Math.pow(10, -i),
	                     u = be[8 + i / 3];
	                 return function(t) {
	                     return r(o * t) + u
	                 }
	             }
	         }
	     }
	     xe = Me({
	         decimal: ".",
	         thousands: ",",
	         grouping: [3],
	         currency: ["$", ""]
	     }), xe.format, xe.formatPrefix;
	     var Te = new Date,
	         Ce = new Date;

	     function ke(t, n, e, r) {
	         function i(n) {
	             return t(n = new Date(+n)), n
	         }
	         return i.floor = i, i.ceil = function(e) {
	             return t(e = new Date(e - 1)), n(e, 1), t(e), e
	         }, i.round = function(t) {
	             var n = i(t),
	                 e = i.ceil(t);
	             return t - n < e - t ? n : e
	         }, i.offset = function(t, e) {
	             return n(t = new Date(+t), null == e ? 1 : Math.floor(e)), t
	         }, i.range = function(e, r, o) {
	             var u, a = [];
	             if (e = i.ceil(e), o = null == o ? 1 : Math.floor(o), !(e < r && o > 0)) return a;
	             do {
	                 a.push(u = new Date(+e)), n(e, o), t(e)
	             } while (u < e && e < r);
	             return a
	         }, i.filter = function(e) {
	             return ke(function(n) {
	                 if (n >= n)
	                     for (; t(n), !e(n);) n.setTime(n - 1)
	             }, function(t, r) {
	                 if (t >= t)
	                     if (r < 0)
	                         for (; ++r <= 0;)
	                             for (; n(t, -1), !e(t););
	                     else
	                         for (; --r >= 0;)
	                             for (; n(t, 1), !e(t););
	             })
	         }, e && (i.count = function(n, r) {
	             return Te.setTime(+n), Ce.setTime(+r), t(Te), t(Ce), Math.floor(e(Te, Ce))
	         }, i.every = function(t) {
	             return t = Math.floor(t), isFinite(t) && t > 0 ? t > 1 ? i.filter(r ? function(n) {
	                 return r(n) % t == 0
	             } : function(n) {
	                 return i.count(0, n) % t == 0
	             }) : i : null
	         }), i
	     }
	     var Ae = ke(function() {}, function(t, n) {
	         t.setTime(+t + n)
	     }, function(t, n) {
	         return n - t
	     });
	     Ae.every = function(t) {
	         return t = Math.floor(t), isFinite(t) && t > 0 ? t > 1 ? ke(function(n) {
	             n.setTime(Math.floor(n / t) * t)
	         }, function(n, e) {
	             n.setTime(+n + e * t)
	         }, function(n, e) {
	             return (e - n) / t
	         }) : Ae : null
	     };
	     Ae.range;
	     var ze = 6e4,
	         Ne = 6048e5,
	         Se = (ke(function(t) {
	             t.setTime(t - t.getMilliseconds())
	         }, function(t, n) {
	             t.setTime(+t + 1e3 * n)
	         }, function(t, n) {
	             return (n - t) / 1e3
	         }, function(t) {
	             return t.getUTCSeconds()
	         }).range, ke(function(t) {
	             t.setTime(t - t.getMilliseconds() - 1e3 * t.getSeconds())
	         }, function(t, n) {
	             t.setTime(+t + n * ze)
	         }, function(t, n) {
	             return (n - t) / ze
	         }, function(t) {
	             return t.getMinutes()
	         }).range, ke(function(t) {
	             t.setTime(t - t.getMilliseconds() - 1e3 * t.getSeconds() - t.getMinutes() * ze)
	         }, function(t, n) {
	             t.setTime(+t + 36e5 * n)
	         }, function(t, n) {
	             return (n - t) / 36e5
	         }, function(t) {
	             return t.getHours()
	         }).range, ke(function(t) {
	             t.setHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setDate(t.getDate() + n)
	         }, function(t, n) {
	             return (n - t - (n.getTimezoneOffset() - t.getTimezoneOffset()) * ze) / 864e5
	         }, function(t) {
	             return t.getDate() - 1
	         }));
	     Se.range;

	     function Ue(t) {
	         return ke(function(n) {
	             n.setDate(n.getDate() - (n.getDay() + 7 - t) % 7), n.setHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setDate(t.getDate() + 7 * n)
	         }, function(t, n) {
	             return (n - t - (n.getTimezoneOffset() - t.getTimezoneOffset()) * ze) / Ne
	         })
	     }
	     var De = Ue(0),
	         Ee = Ue(1),
	         Ye = (Ue(2), Ue(3), Ue(4)),
	         Pe = (Ue(5), Ue(6), De.range, ke(function(t) {
	             t.setDate(1), t.setHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setMonth(t.getMonth() + n)
	         }, function(t, n) {
	             return n.getMonth() - t.getMonth() + 12 * (n.getFullYear() - t.getFullYear())
	         }, function(t) {
	             return t.getMonth()
	         }).range, ke(function(t) {
	             t.setMonth(0, 1), t.setHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setFullYear(t.getFullYear() + n)
	         }, function(t, n) {
	             return n.getFullYear() - t.getFullYear()
	         }, function(t) {
	             return t.getFullYear()
	         }));
	     Pe.every = function(t) {
	         return isFinite(t = Math.floor(t)) && t > 0 ? ke(function(n) {
	             n.setFullYear(Math.floor(n.getFullYear() / t) * t), n.setMonth(0, 1), n.setHours(0, 0, 0, 0)
	         }, function(n, e) {
	             n.setFullYear(n.getFullYear() + e * t)
	         }) : null
	     };
	     Pe.range, ke(function(t) {
	         t.setUTCSeconds(0, 0)
	     }, function(t, n) {
	         t.setTime(+t + n * ze)
	     }, function(t, n) {
	         return (n - t) / ze
	     }, function(t) {
	         return t.getUTCMinutes()
	     }).range, ke(function(t) {
	         t.setUTCMinutes(0, 0, 0)
	     }, function(t, n) {
	         t.setTime(+t + 36e5 * n)
	     }, function(t, n) {
	         return (n - t) / 36e5
	     }, function(t) {
	         return t.getUTCHours()
	     }).range;
	     var He = ke(function(t) {
	         t.setUTCHours(0, 0, 0, 0)
	     }, function(t, n) {
	         t.setUTCDate(t.getUTCDate() + n)
	     }, function(t, n) {
	         return (n - t) / 864e5
	     }, function(t) {
	         return t.getUTCDate() - 1
	     });
	     He.range;

	     function Fe(t) {
	         return ke(function(n) {
	             n.setUTCDate(n.getUTCDate() - (n.getUTCDay() + 7 - t) % 7), n.setUTCHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setUTCDate(t.getUTCDate() + 7 * n)
	         }, function(t, n) {
	             return (n - t) / Ne
	         })
	     }
	     var je = Fe(0),
	         Be = Fe(1),
	         Le = (Fe(2), Fe(3), Fe(4)),
	         Xe = (Fe(5), Fe(6), je.range, ke(function(t) {
	             t.setUTCDate(1), t.setUTCHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setUTCMonth(t.getUTCMonth() + n)
	         }, function(t, n) {
	             return n.getUTCMonth() - t.getUTCMonth() + 12 * (n.getUTCFullYear() - t.getUTCFullYear())
	         }, function(t) {
	             return t.getUTCMonth()
	         }).range, ke(function(t) {
	             t.setUTCMonth(0, 1), t.setUTCHours(0, 0, 0, 0)
	         }, function(t, n) {
	             t.setUTCFullYear(t.getUTCFullYear() + n)
	         }, function(t, n) {
	             return n.getUTCFullYear() - t.getUTCFullYear()
	         }, function(t) {
	             return t.getUTCFullYear()
	         }));
	     Xe.every = function(t) {
	         return isFinite(t = Math.floor(t)) && t > 0 ? ke(function(n) {
	             n.setUTCFullYear(Math.floor(n.getUTCFullYear() / t) * t), n.setUTCMonth(0, 1), n.setUTCHours(0, 0, 0, 0)
	         }, function(n, e) {
	             n.setUTCFullYear(n.getUTCFullYear() + e * t)
	         }) : null
	     };
	     Xe.range;

	     function Ie(t) {
	         if (0 <= t.y && t.y < 100) {
	             var n = new Date(-1, t.m, t.d, t.H, t.M, t.S, t.L);
	             return n.setFullYear(t.y), n
	         }
	         return new Date(t.y, t.m, t.d, t.H, t.M, t.S, t.L)
	     }

	     function $e(t) {
	         if (0 <= t.y && t.y < 100) {
	             var n = new Date(Date.UTC(-1, t.m, t.d, t.H, t.M, t.S, t.L));
	             return n.setUTCFullYear(t.y), n
	         }
	         return new Date(Date.UTC(t.y, t.m, t.d, t.H, t.M, t.S, t.L))
	     }

	     function Oe(t) {
	         return {
	             y: t,
	             m: 0,
	             d: 1,
	             H: 0,
	             M: 0,
	             S: 0,
	             L: 0
	         }
	     }
	     var qe, Ve, Re, We = {
	             "-": "",
	             _: " ",
	             0: "0"
	         },
	         Ze = /^\s*\d+/,
	         Qe = /^%/,
	         Je = /[\\^$*+?|[\]().{}]/g;

	     function Ge(t, n, e) {
	         var r = t < 0 ? "-" : "",
	             i = (r ? -t : t) + "",
	             o = i.length;
	         return r + (o < e ? new Array(e - o + 1).join(n) + i : i)
	     }

	     function Ke(t) {
	         return t.replace(Je, "\\$&")
	     }

	     function tr(t) {
	         return new RegExp("^(?:" + t.map(Ke).join("|") + ")", "i")
	     }

	     function nr(t) {
	         for (var n = {}, e = -1, r = t.length; ++e < r;) n[t[e].toLowerCase()] = e;
	         return n
	     }

	     function er(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 1));
	         return r ? (t.w = +r[0], e + r[0].length) : -1
	     }

	     function rr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 1));
	         return r ? (t.u = +r[0], e + r[0].length) : -1
	     }

	     function ir(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.U = +r[0], e + r[0].length) : -1
	     }

	     function or(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.V = +r[0], e + r[0].length) : -1
	     }

	     function ur(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.W = +r[0], e + r[0].length) : -1
	     }

	     function ar(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 4));
	         return r ? (t.y = +r[0], e + r[0].length) : -1
	     }

	     function sr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.y = +r[0] + (+r[0] > 68 ? 1900 : 2e3), e + r[0].length) : -1
	     }

	     function cr(t, n, e) {
	         var r = /^(Z)|([+-]\d\d)(?::?(\d\d))?/.exec(n.slice(e, e + 6));
	         return r ? (t.Z = r[1] ? 0 : -(r[2] + (r[3] || "00")), e + r[0].length) : -1
	     }

	     function hr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.m = r[0] - 1, e + r[0].length) : -1
	     }

	     function lr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.d = +r[0], e + r[0].length) : -1
	     }

	     function fr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 3));
	         return r ? (t.m = 0, t.d = +r[0], e + r[0].length) : -1
	     }

	     function pr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.H = +r[0], e + r[0].length) : -1
	     }

	     function dr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.M = +r[0], e + r[0].length) : -1
	     }

	     function gr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 2));
	         return r ? (t.S = +r[0], e + r[0].length) : -1
	     }

	     function vr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 3));
	         return r ? (t.L = +r[0], e + r[0].length) : -1
	     }

	     function mr(t, n, e) {
	         var r = Ze.exec(n.slice(e, e + 6));
	         return r ? (t.L = Math.floor(r[0] / 1e3), e + r[0].length) : -1
	     }

	     function yr(t, n, e) {
	         var r = Qe.exec(n.slice(e, e + 1));
	         return r ? e + r[0].length : -1
	     }

	     function _r(t, n, e) {
	         var r = Ze.exec(n.slice(e));
	         return r ? (t.Q = +r[0], e + r[0].length) : -1
	     }

	     function wr(t, n, e) {
	         var r = Ze.exec(n.slice(e));
	         return r ? (t.Q = 1e3 * +r[0], e + r[0].length) : -1
	     }

	     function xr(t, n) {
	         return Ge(t.getDate(), n, 2)
	     }

	     function br(t, n) {
	         return Ge(t.getHours(), n, 2)
	     }

	     function Mr(t, n) {
	         return Ge(t.getHours() % 12 || 12, n, 2)
	     }

	     function Tr(t, n) {
	         return Ge(1 + Se.count(Pe(t), t), n, 3)
	     }

	     function Cr(t, n) {
	         return Ge(t.getMilliseconds(), n, 3)
	     }

	     function kr(t, n) {
	         return Cr(t, n) + "000"
	     }

	     function Ar(t, n) {
	         return Ge(t.getMonth() + 1, n, 2)
	     }

	     function zr(t, n) {
	         return Ge(t.getMinutes(), n, 2)
	     }

	     function Nr(t, n) {
	         return Ge(t.getSeconds(), n, 2)
	     }

	     function Sr(t) {
	         var n = t.getDay();
	         return 0 === n ? 7 : n
	     }

	     function Ur(t, n) {
	         return Ge(De.count(Pe(t), t), n, 2)
	     }

	     function Dr(t, n) {
	         var e = t.getDay();
	         return t = e >= 4 || 0 === e ? Ye(t) : Ye.ceil(t), Ge(Ye.count(Pe(t), t) + (4 === Pe(t).getDay()), n, 2)
	     }

	     function Er(t) {
	         return t.getDay()
	     }

	     function Yr(t, n) {
	         return Ge(Ee.count(Pe(t), t), n, 2)
	     }

	     function Pr(t, n) {
	         return Ge(t.getFullYear() % 100, n, 2)
	     }

	     function Hr(t, n) {
	         return Ge(t.getFullYear() % 1e4, n, 4)
	     }

	     function Fr(t) {
	         var n = t.getTimezoneOffset();
	         return (n > 0 ? "-" : (n *= -1, "+")) + Ge(n / 60 | 0, "0", 2) + Ge(n % 60, "0", 2)
	     }

	     function jr(t, n) {
	         return Ge(t.getUTCDate(), n, 2)
	     }

	     function Br(t, n) {
	         return Ge(t.getUTCHours(), n, 2)
	     }

	     function Lr(t, n) {
	         return Ge(t.getUTCHours() % 12 || 12, n, 2)
	     }

	     function Xr(t, n) {
	         return Ge(1 + He.count(Xe(t), t), n, 3)
	     }

	     function Ir(t, n) {
	         return Ge(t.getUTCMilliseconds(), n, 3)
	     }

	     function $r(t, n) {
	         return Ir(t, n) + "000"
	     }

	     function Or(t, n) {
	         return Ge(t.getUTCMonth() + 1, n, 2)
	     }

	     function qr(t, n) {
	         return Ge(t.getUTCMinutes(), n, 2)
	     }

	     function Vr(t, n) {
	         return Ge(t.getUTCSeconds(), n, 2)
	     }

	     function Rr(t) {
	         var n = t.getUTCDay();
	         return 0 === n ? 7 : n
	     }

	     function Wr(t, n) {
	         return Ge(je.count(Xe(t), t), n, 2)
	     }

	     function Zr(t, n) {
	         var e = t.getUTCDay();
	         return t = e >= 4 || 0 === e ? Le(t) : Le.ceil(t), Ge(Le.count(Xe(t), t) + (4 === Xe(t).getUTCDay()), n, 2)
	     }

	     function Qr(t) {
	         return t.getUTCDay()
	     }

	     function Jr(t, n) {
	         return Ge(Be.count(Xe(t), t), n, 2)
	     }

	     function Gr(t, n) {
	         return Ge(t.getUTCFullYear() % 100, n, 2)
	     }

	     function Kr(t, n) {
	         return Ge(t.getUTCFullYear() % 1e4, n, 4)
	     }

	     function ti() {
	         return "+0000"
	     }

	     function ni() {
	         return "%"
	     }

	     function ei(t) {
	         return +t
	     }

	     function ri(t) {
	         return Math.floor(+t / 1e3)
	     }! function(t) {
	         qe = function(t) {
	             var n = t.dateTime,
	                 e = t.date,
	                 r = t.time,
	                 i = t.periods,
	                 o = t.days,
	                 u = t.shortDays,
	                 a = t.months,
	                 s = t.shortMonths,
	                 c = tr(i),
	                 h = nr(i),
	                 l = tr(o),
	                 f = nr(o),
	                 p = tr(u),
	                 d = nr(u),
	                 g = tr(a),
	                 v = nr(a),
	                 m = tr(s),
	                 y = nr(s),
	                 _ = {
	                     a: function(t) {
	                         return u[t.getDay()]
	                     },
	                     A: function(t) {
	                         return o[t.getDay()]
	                     },
	                     b: function(t) {
	                         return s[t.getMonth()]
	                     },
	                     B: function(t) {
	                         return a[t.getMonth()]
	                     },
	                     c: null,
	                     d: xr,
	                     e: xr,
	                     f: kr,
	                     H: br,
	                     I: Mr,
	                     j: Tr,
	                     L: Cr,
	                     m: Ar,
	                     M: zr,
	                     p: function(t) {
	                         return i[+(t.getHours() >= 12)]
	                     },
	                     Q: ei,
	                     s: ri,
	                     S: Nr,
	                     u: Sr,
	                     U: Ur,
	                     V: Dr,
	                     w: Er,
	                     W: Yr,
	                     x: null,
	                     X: null,
	                     y: Pr,
	                     Y: Hr,
	                     Z: Fr,
	                     "%": ni
	                 },
	                 w = {
	                     a: function(t) {
	                         return u[t.getUTCDay()]
	                     },
	                     A: function(t) {
	                         return o[t.getUTCDay()]
	                     },
	                     b: function(t) {
	                         return s[t.getUTCMonth()]
	                     },
	                     B: function(t) {
	                         return a[t.getUTCMonth()]
	                     },
	                     c: null,
	                     d: jr,
	                     e: jr,
	                     f: $r,
	                     H: Br,
	                     I: Lr,
	                     j: Xr,
	                     L: Ir,
	                     m: Or,
	                     M: qr,
	                     p: function(t) {
	                         return i[+(t.getUTCHours() >= 12)]
	                     },
	                     Q: ei,
	                     s: ri,
	                     S: Vr,
	                     u: Rr,
	                     U: Wr,
	                     V: Zr,
	                     w: Qr,
	                     W: Jr,
	                     x: null,
	                     X: null,
	                     y: Gr,
	                     Y: Kr,
	                     Z: ti,
	                     "%": ni
	                 },
	                 x = {
	                     a: function(t, n, e) {
	                         var r = p.exec(n.slice(e));
	                         return r ? (t.w = d[r[0].toLowerCase()], e + r[0].length) : -1
	                     },
	                     A: function(t, n, e) {
	                         var r = l.exec(n.slice(e));
	                         return r ? (t.w = f[r[0].toLowerCase()], e + r[0].length) : -1
	                     },
	                     b: function(t, n, e) {
	                         var r = m.exec(n.slice(e));
	                         return r ? (t.m = y[r[0].toLowerCase()], e + r[0].length) : -1
	                     },
	                     B: function(t, n, e) {
	                         var r = g.exec(n.slice(e));
	                         return r ? (t.m = v[r[0].toLowerCase()], e + r[0].length) : -1
	                     },
	                     c: function(t, e, r) {
	                         return T(t, n, e, r)
	                     },
	                     d: lr,
	                     e: lr,
	                     f: mr,
	                     H: pr,
	                     I: pr,
	                     j: fr,
	                     L: vr,
	                     m: hr,
	                     M: dr,
	                     p: function(t, n, e) {
	                         var r = c.exec(n.slice(e));
	                         return r ? (t.p = h[r[0].toLowerCase()], e + r[0].length) : -1
	                     },
	                     Q: _r,
	                     s: wr,
	                     S: gr,
	                     u: rr,
	                     U: ir,
	                     V: or,
	                     w: er,
	                     W: ur,
	                     x: function(t, n, r) {
	                         return T(t, e, n, r)
	                     },
	                     X: function(t, n, e) {
	                         return T(t, r, n, e)
	                     },
	                     y: sr,
	                     Y: ar,
	                     Z: cr,
	                     "%": yr
	                 };

	             function b(t, n) {
	                 return function(e) {
	                     var r, i, o, u = [],
	                         a = -1,
	                         s = 0,
	                         c = t.length;
	                     for (e instanceof Date || (e = new Date(+e)); ++a < c;) 37 === t.charCodeAt(a) && (u.push(t.slice(s, a)), null != (i = We[r = t.charAt(++a)]) ? r = t.charAt(++a) : i = "e" === r ? " " : "0", (o = n[r]) && (r = o(e, i)), u.push(r), s = a + 1);
	                     return u.push(t.slice(s, a)), u.join("")
	                 }
	             }

	             function M(t, n) {
	                 return function(e) {
	                     var r, i, o = Oe(1900);
	                     if (T(o, t, e += "", 0) != e.length) return null;
	                     if ("Q" in o) return new Date(o.Q);
	                     if ("p" in o && (o.H = o.H % 12 + 12 * o.p), "V" in o) {
	                         if (o.V < 1 || o.V > 53) return null;
	                         "w" in o || (o.w = 1), "Z" in o ? (r = (i = (r = $e(Oe(o.y))).getUTCDay()) > 4 || 0 === i ? Be.ceil(r) : Be(r), r = He.offset(r, 7 * (o.V - 1)), o.y = r.getUTCFullYear(), o.m = r.getUTCMonth(), o.d = r.getUTCDate() + (o.w + 6) % 7) : (r = (i = (r = n(Oe(o.y))).getDay()) > 4 || 0 === i ? Ee.ceil(r) : Ee(r), r = Se.offset(r, 7 * (o.V - 1)), o.y = r.getFullYear(), o.m = r.getMonth(), o.d = r.getDate() + (o.w + 6) % 7)
	                     } else("W" in o || "U" in o) && ("w" in o || (o.w = "u" in o ? o.u % 7 : "W" in o ? 1 : 0), i = "Z" in o ? $e(Oe(o.y)).getUTCDay() : n(Oe(o.y)).getDay(), o.m = 0, o.d = "W" in o ? (o.w + 6) % 7 + 7 * o.W - (i + 5) % 7 : o.w + 7 * o.U - (i + 6) % 7);
	                     return "Z" in o ? (o.H += o.Z / 100 | 0, o.M += o.Z % 100, $e(o)) : n(o)
	                 }
	             }

	             function T(t, n, e, r) {
	                 for (var i, o, u = 0, a = n.length, s = e.length; u < a;) {
	                     if (r >= s) return -1;
	                     if (37 === (i = n.charCodeAt(u++))) {
	                         if (i = n.charAt(u++), !(o = x[i in We ? n.charAt(u++) : i]) || (r = o(t, e, r)) < 0) return -1
	                     } else if (i != e.charCodeAt(r++)) return -1
	                 }
	                 return r
	             }
	             return _.x = b(e, _), _.X = b(r, _), _.c = b(n, _), w.x = b(e, w), w.X = b(r, w), w.c = b(n, w), {
	                 format: function(t) {
	                     var n = b(t += "", _);
	                     return n.toString = function() {
	                         return t
	                     }, n
	                 },
	                 parse: function(t) {
	                     var n = M(t += "", Ie);
	                     return n.toString = function() {
	                         return t
	                     }, n
	                 },
	                 utcFormat: function(t) {
	                     var n = b(t += "", w);
	                     return n.toString = function() {
	                         return t
	                     }, n
	                 },
	                 utcParse: function(t) {
	                     var n = M(t, $e);
	                     return n.toString = function() {
	                         return t
	                     }, n
	                 }
	             }
	         }(t), qe.format, qe.parse, Ve = qe.utcFormat, Re = qe.utcParse
	     }({
	         dateTime: "%x, %X",
	         date: "%-m/%-d/%Y",
	         time: "%-I:%M:%S %p",
	         periods: ["AM", "PM"],
	         days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
	         shortDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
	         months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
	         shortMonths: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
	     });
	     Date.prototype.toISOString || Ve("%Y-%m-%dT%H:%M:%S.%LZ"); + new Date("2000-01-01T00:00:00.000Z") || Re("%Y-%m-%dT%H:%M:%S.%LZ");

	     function ii() {
	         D.preventDefault(), D.stopImmediatePropagation()
	     }

	     function oi(t) {
	         return function() {
	             return t
	         }
	     }

	     function ui(t, n, e) {
	         this.k = t, this.x = n, this.y = e
	     }
	     ui.prototype = {
	         constructor: ui,
	         scale: function(t) {
	             return 1 === t ? this : new ui(this.k * t, this.x, this.y)
	         },
	         translate: function(t, n) {
	             return 0 === t & 0 === n ? this : new ui(this.k, this.x + this.k * t, this.y + this.k * n)
	         },
	         apply: function(t) {
	             return [t[0] * this.k + this.x, t[1] * this.k + this.y]
	         },
	         applyX: function(t) {
	             return t * this.k + this.x
	         },
	         applyY: function(t) {
	             return t * this.k + this.y
	         },
	         invert: function(t) {
	             return [(t[0] - this.x) / this.k, (t[1] - this.y) / this.k]
	         },
	         invertX: function(t) {
	             return (t - this.x) / this.k
	         },
	         invertY: function(t) {
	             return (t - this.y) / this.k
	         },
	         rescaleX: function(t) {
	             return t.copy().domain(t.range().map(this.invertX, this).map(t.invert, t))
	         },
	         rescaleY: function(t) {
	             return t.copy().domain(t.range().map(this.invertY, this).map(t.invert, t))
	         },
	         toString: function() {
	             return "translate(" + this.x + "," + this.y + ") scale(" + this.k + ")"
	         }
	     };
	     var ai = new ui(1, 0, 0);

	     function si() {
	         D.stopImmediatePropagation()
	     }

	     function ci() {
	         D.preventDefault(), D.stopImmediatePropagation()
	     }

	     function hi() {
	         return !D.button
	     }

	     function li() {
	         var t, n, e = this;
	         return e instanceof SVGElement ? (t = (e = e.ownerSVGElement || e).width.baseVal.value, n = e.height.baseVal.value) : (t = e.clientWidth, n = e.clientHeight), [
	             [0, 0],
	             [t, n]
	         ]
	     }

	     function fi() {
	         return this.__zoom || ai
	     }

	     function pi() {
	         return -D.deltaY * (D.deltaMode ? 120 : 1) / 500
	     }

	     function di() {
	         return "ontouchstart" in this
	     }

	     function gi(t, n, e) {
	         var r = t.invertX(n[0][0]) - e[0][0],
	             i = t.invertX(n[1][0]) - e[1][0],
	             o = t.invertY(n[0][1]) - e[0][1],
	             u = t.invertY(n[1][1]) - e[1][1];
	         return t.translate(i > r ? (r + i) / 2 : Math.min(0, r) || Math.max(0, i), u > o ? (o + u) / 2 : Math.min(0, o) || Math.max(0, u))
	     }

	     function vi() {
	         var t, n, e = hi,
	             r = li,
	             i = gi,
	             o = pi,
	             u = di,
	             a = [0, 1 / 0],
	             s = [
	                 [-1 / 0, -1 / 0],
	                 [1 / 0, 1 / 0]
	             ],
	             c = 250,
	             h = Zn,
	             l = [],
	             f = R("start", "zoom", "end"),
	             p = 500,
	             d = 150,
	             g = 0;

	         function v(t) {
	             t.property("__zoom", fi).on("wheel.zoom", M).on("mousedown.zoom", T).on("dblclick.zoom", C).filter(u).on("touchstart.zoom", k).on("touchmove.zoom", A).on("touchend.zoom touchcancel.zoom", z).style("touch-action", "none").style("-webkit-tap-highlight-color", "rgba(0,0,0,0)")
	         }

	         function m(t, n) {
	             return (n = Math.max(a[0], Math.min(a[1], n))) === t.k ? t : new ui(n, t.x, t.y)
	         }

	         function y(t, n, e) {
	             var r = n[0] - e[0] * t.k,
	                 i = n[1] - e[1] * t.k;
	             return r === t.x && i === t.y ? t : new ui(t.k, r, i)
	         }

	         function _(t) {
	             return [(+t[0][0] + +t[1][0]) / 2, (+t[0][1] + +t[1][1]) / 2]
	         }

	         function w(t, n, e) {
	             t.on("start.zoom", function() {
	                 x(this, arguments).start()
	             }).on("interrupt.zoom end.zoom", function() {
	                 x(this, arguments).end()
	             }).tween("zoom", function() {
	                 var t = arguments,
	                     i = x(this, t),
	                     o = r.apply(this, t),
	                     u = e || _(o),
	                     a = Math.max(o[1][0] - o[0][0], o[1][1] - o[0][1]),
	                     s = this.__zoom,
	                     c = "function" == typeof n ? n.apply(this, t) : n,
	                     l = h(s.invert(u).concat(a / s.k), c.invert(u).concat(a / c.k));
	                 return function(t) {
	                     if (1 === t) t = c;
	                     else {
	                         var n = l(t),
	                             e = a / n[2];
	                         t = new ui(e, u[0] - n[0] * e, u[1] - n[1] * e)
	                     }
	                     i.zoom(null, t)
	                 }
	             })
	         }

	         function x(t, n) {
	             for (var e, r = 0, i = l.length; r < i; ++r)
	                 if ((e = l[r]).that === t) return e;
	             return new b(t, n)
	         }

	         function b(t, n) {
	             this.that = t, this.args = n, this.index = -1, this.active = 0, this.extent = r.apply(t, n)
	         }

	         function M() {
	             if (e.apply(this, arguments)) {
	                 var t = x(this, arguments),
	                     n = this.__zoom,
	                     r = Math.max(a[0], Math.min(a[1], n.k * Math.pow(2, o.apply(this, arguments)))),
	                     u = O(this);
	                 if (t.wheel) t.mouse[0][0] === u[0] && t.mouse[0][1] === u[1] || (t.mouse[1] = n.invert(t.mouse[0] = u)), clearTimeout(t.wheel);
	                 else {
	                     if (n.k === r) return;
	                     t.mouse = [u, n.invert(u)], Nt(this), t.start()
	                 }
	                 ci(), t.wheel = setTimeout(function() {
	                     t.wheel = null, t.end()
	                 }, d), t.zoom("mouse", i(y(m(n, r), t.mouse[0], t.mouse[1]), t.extent, s))
	             }
	         }

	         function T() {
	             if (!n && e.apply(this, arguments)) {
	                 var t, r, o, u = x(this, arguments),
	                     a = X(D.view).on("mousemove.zoom", function() {
	                         if (ci(), !u.moved) {
	                             var t = D.clientX - h,
	                                 n = D.clientY - l;
	                             u.moved = t * t + n * n > g
	                         }
	                         u.zoom("mouse", i(y(u.that.__zoom, u.mouse[0] = O(u.that), u.mouse[1]), u.extent, s))
	                     }, !0).on("mouseup.zoom", function() {
	                         a.on("mousemove.zoom mouseup.zoom", null), t = D.view, n = u.moved, e = t.document.documentElement, r = X(t).on("dragstart.drag", null), n && (r.on("click.drag", ii, !0), setTimeout(function() {
	                             r.on("click.drag", null)
	                         }, 0)), "onselectstart" in e ? r.on("selectstart.drag", null) : (e.style.MozUserSelect = e.__noselect, delete e.__noselect), ci(), u.end();
	                         var t, n, e, r
	                     }, !0),
	                     c = O(this),
	                     h = D.clientX,
	                     l = D.clientY;
	                 t = D.view, r = t.document.documentElement, o = X(t).on("dragstart.drag", ii, !0), "onselectstart" in r ? o.on("selectstart.drag", ii, !0) : (r.__noselect = r.style.MozUserSelect, r.style.MozUserSelect = "none"), si(), u.mouse = [c, this.__zoom.invert(c)], Nt(this), u.start()
	             }
	         }

	         function C() {
	             if (e.apply(this, arguments)) {
	                 var t = this.__zoom,
	                     n = O(this),
	                     o = t.invert(n),
	                     u = t.k * (D.shiftKey ? .5 : 2),
	                     a = i(y(m(t, u), n, o), r.apply(this, arguments), s);
	                 ci(), c > 0 ? X(this).transition().duration(c).call(w, a, n) : X(this).call(v.transform, a)
	             }
	         }

	         function k() {
	             if (e.apply(this, arguments)) {
	                 var n, r, i, o, u = x(this, arguments),
	                     a = D.changedTouches,
	                     s = a.length;
	                 for (si(), r = 0; r < s; ++r) o = [o = q(this, a, (i = a[r]).identifier), this.__zoom.invert(o), i.identifier], u.touch0 ? u.touch1 || (u.touch1 = o) : (u.touch0 = o, n = !0);
	                 if (t && (t = clearTimeout(t), !u.touch1)) return u.end(), void((o = X(this).on("dblclick.zoom")) && o.apply(this, arguments));
	                 n && (t = setTimeout(function() {
	                     t = null
	                 }, p), Nt(this), u.start())
	             }
	         }

	         function A() {
	             var n, e, r, o, u = x(this, arguments),
	                 a = D.changedTouches,
	                 c = a.length;
	             for (ci(), t && (t = clearTimeout(t)), n = 0; n < c; ++n) r = q(this, a, (e = a[n]).identifier), u.touch0 && u.touch0[2] === e.identifier ? u.touch0[0] = r : u.touch1 && u.touch1[2] === e.identifier && (u.touch1[0] = r);
	             if (e = u.that.__zoom, u.touch1) {
	                 var h = u.touch0[0],
	                     l = u.touch0[1],
	                     f = u.touch1[0],
	                     p = u.touch1[1],
	                     d = (d = f[0] - h[0]) * d + (d = f[1] - h[1]) * d,
	                     g = (g = p[0] - l[0]) * g + (g = p[1] - l[1]) * g;
	                 e = m(e, Math.sqrt(d / g)), r = [(h[0] + f[0]) / 2, (h[1] + f[1]) / 2], o = [(l[0] + p[0]) / 2, (l[1] + p[1]) / 2]
	             } else {
	                 if (!u.touch0) return;
	                 r = u.touch0[0], o = u.touch0[1]
	             }
	             u.zoom("touch", i(y(e, r, o), u.extent, s))
	         }

	         function z() {
	             var t, e, r = x(this, arguments),
	                 i = D.changedTouches,
	                 o = i.length;
	             for (si(), n && clearTimeout(n), n = setTimeout(function() {
	                     n = null
	                 }, p), t = 0; t < o; ++t) e = i[t], r.touch0 && r.touch0[2] === e.identifier ? delete r.touch0 : r.touch1 && r.touch1[2] === e.identifier && delete r.touch1;
	             r.touch1 && !r.touch0 && (r.touch0 = r.touch1, delete r.touch1), r.touch0 ? r.touch0[1] = this.__zoom.invert(r.touch0[0]) : r.end()
	         }
	         return v.transform = function(t, n) {
	             var e = t.selection ? t.selection() : t;
	             e.property("__zoom", fi), t !== e ? w(t, n) : e.interrupt().each(function() {
	                 x(this, arguments).start().zoom(null, "function" == typeof n ? n.apply(this, arguments) : n).end()
	             })
	         }, v.scaleBy = function(t, n) {
	             v.scaleTo(t, function() {
	                 return this.__zoom.k * ("function" == typeof n ? n.apply(this, arguments) : n)
	             })
	         }, v.scaleTo = function(t, n) {
	             v.transform(t, function() {
	                 var t = r.apply(this, arguments),
	                     e = this.__zoom,
	                     o = _(t),
	                     u = e.invert(o),
	                     a = "function" == typeof n ? n.apply(this, arguments) : n;
	                 return i(y(m(e, a), o, u), t, s)
	             })
	         }, v.translateBy = function(t, n, e) {
	             v.transform(t, function() {
	                 return i(this.__zoom.translate("function" == typeof n ? n.apply(this, arguments) : n, "function" == typeof e ? e.apply(this, arguments) : e), r.apply(this, arguments), s)
	             })
	         }, v.translateTo = function(t, n, e) {
	             v.transform(t, function() {
	                 var t = r.apply(this, arguments),
	                     o = this.__zoom,
	                     u = _(t);
	                 return i(ai.translate(u[0], u[1]).scale(o.k).translate("function" == typeof n ? -n.apply(this, arguments) : -n, "function" == typeof e ? -e.apply(this, arguments) : -e), t, s)
	             })
	         }, b.prototype = {
	             start: function() {
	                 return 1 == ++this.active && (this.index = l.push(this) - 1, this.emit("start")), this
	             },
	             zoom: function(t, n) {
	                 return this.mouse && "mouse" !== t && (this.mouse[1] = n.invert(this.mouse[0])), this.touch0 && "touch" !== t && (this.touch0[1] = n.invert(this.touch0[0])), this.touch1 && "touch" !== t && (this.touch1[1] = n.invert(this.touch1[0])), this.that.__zoom = n, this.emit("zoom"), this
	             },
	             end: function() {
	                 return 0 == --this.active && (l.splice(this.index, 1), this.index = -1, this.emit("end")), this
	             },
	             emit: function(t) {
	                 ! function(t, n, e, r) {
	                     var i = D;
	                     t.sourceEvent = D, D = t;
	                     try {
	                         n.apply(e, r)
	                     } finally {
	                         D = i
	                     }
	                 }(new function(t, n, e) {
	                     this.target = t, this.type = n, this.transform = e
	                 }(v, t, this.that.__zoom), f.apply, f, [t, this.that, this.args])
	             }
	         }, v.wheelDelta = function(t) {
	             return arguments.length ? (o = "function" == typeof t ? t : oi(+t), v) : o
	         }, v.filter = function(t) {
	             return arguments.length ? (e = "function" == typeof t ? t : oi(!!t), v) : e
	         }, v.touchable = function(t) {
	             return arguments.length ? (u = "function" == typeof t ? t : oi(!!t), v) : u
	         }, v.extent = function(t) {
	             return arguments.length ? (r = "function" == typeof t ? t : oi([
	                 [+t[0][0], +t[0][1]],
	                 [+t[1][0], +t[1][1]]
	             ]), v) : r
	         }, v.scaleExtent = function(t) {
	             return arguments.length ? (a[0] = +t[0], a[1] = +t[1], v) : [a[0], a[1]]
	         }, v.translateExtent = function(t) {
	             return arguments.length ? (s[0][0] = +t[0][0], s[1][0] = +t[1][0], s[0][1] = +t[0][1], s[1][1] = +t[1][1], v) : [
	                 [s[0][0], s[0][1]],
	                 [s[1][0], s[1][1]]
	             ]
	         }, v.constrain = function(t) {
	             return arguments.length ? (i = t, v) : i
	         }, v.duration = function(t) {
	             return arguments.length ? (c = +t, v) : c
	         }, v.interpolate = function(t) {
	             return arguments.length ? (h = t, v) : h
	         }, v.on = function() {
	             var t = f.on.apply(f, arguments);
	             return t === f ? v : t
	         }, v.clickDistance = function(t) {
	             return arguments.length ? (g = (t = +t) * t, v) : Math.sqrt(g)
	         }, v
	     }

	     function mi(t) {
	         var n = 0,
	             e = t.children,
	             r = e && e.length;
	         if (r)
	             for (; --r >= 0;) n += e[r].value;
	         else n = 1;
	         t.value = n
	     }

	     function yi(t, n) {
	         var e, r, i, o, u, a = new bi(t),
	             s = +t.value && (a.value = t.value),
	             c = [a];
	         for (null == n && (n = _i); e = c.pop();)
	             if (s && (e.value = +e.data.value), (i = n(e.data)) && (u = i.length))
	                 for (e.children = new Array(u), o = u - 1; o >= 0; --o) c.push(r = e.children[o] = new bi(i[o])), r.parent = e, r.depth = e.depth + 1;
	         return a.eachBefore(xi)
	     }

	     function _i(t) {
	         return t.children
	     }

	     function wi(t) {
	         t.data = t.data.data
	     }

	     function xi(t) {
	         var n = 0;
	         do {
	             t.height = n
	         } while ((t = t.parent) && t.height < ++n)
	     }

	     function bi(t) {
	         this.data = t, this.depth = this.height = 0, this.parent = null
	     }

	     function Mi(t, n) {
	         return t.parent === n.parent ? 1 : 2
	     }

	     function Ti(t) {
	         var n = t.children;
	         return n ? n[0] : t.t
	     }

	     function Ci(t) {
	         var n = t.children;
	         return n ? n[n.length - 1] : t.t
	     }

	     function ki(t, n, e) {
	         var r = e / (n.i - t.i);
	         n.c -= r, n.s += e, t.c += r, n.z += e, n.m += e
	     }

	     function Ai(t, n, e) {
	         return t.a.parent === n.parent ? t.a : e
	     }

	     function zi(t, n) {
	         this._ = t, this.parent = null, this.children = null, this.A = null, this.a = this, this.z = 0, this.m = 0, this.c = 0, this.s = 0, this.t = null, this.i = n
	     }

	     function Ni() {
	         var t = Mi,
	             n = 1,
	             e = 1,
	             r = null;

	         function i(i) {
	             var s = function(t) {
	                 for (var n, e, r, i, o, u = new zi(t, 0), a = [u]; n = a.pop();)
	                     if (r = n._.children)
	                         for (n.children = new Array(o = r.length), i = o - 1; i >= 0; --i) a.push(e = n.children[i] = new zi(r[i], i)), e.parent = n;
	                 return (u.parent = new zi(null, 0)).children = [u], u
	             }(i);
	             if (s.eachAfter(o), s.parent.m = -s.z, s.eachBefore(u), r) i.eachBefore(a);
	             else {
	                 var c = i,
	                     h = i,
	                     l = i;
	                 i.eachBefore(function(t) {
	                     t.x < c.x && (c = t), t.x > h.x && (h = t), t.depth > l.depth && (l = t)
	                 });
	                 var f = c === h ? 1 : t(c, h) / 2,
	                     p = f - c.x,
	                     d = n / (h.x + f + p),
	                     g = e / (l.depth || 1);
	                 i.eachBefore(function(t) {
	                     t.x = (t.x + p) * d, t.y = t.depth * g
	                 })
	             }
	             return i
	         }

	         function o(n) {
	             var e = n.children,
	                 r = n.parent.children,
	                 i = n.i ? r[n.i - 1] : null;
	             if (e) {
	                 ! function(t) {
	                     for (var n, e = 0, r = 0, i = t.children, o = i.length; --o >= 0;)(n = i[o]).z += e, n.m += e, e += n.s + (r += n.c)
	                 }(n);
	                 var o = (e[0].z + e[e.length - 1].z) / 2;
	                 i ? (n.z = i.z + t(n._, i._), n.m = n.z - o) : n.z = o
	             } else i && (n.z = i.z + t(n._, i._));
	             n.parent.A = function(n, e, r) {
	                 if (e) {
	                     for (var i, o = n, u = n, a = e, s = o.parent.children[0], c = o.m, h = u.m, l = a.m, f = s.m; a = Ci(a), o = Ti(o), a && o;) s = Ti(s), (u = Ci(u)).a = n, (i = a.z + l - o.z - c + t(a._, o._)) > 0 && (ki(Ai(a, n, r), n, i), c += i, h += i), l += a.m, c += o.m, f += s.m, h += u.m;
	                     a && !Ci(u) && (u.t = a, u.m += l - h), o && !Ti(s) && (s.t = o, s.m += c - f, r = n)
	                 }
	                 return r
	             }(n, i, n.parent.A || r[0])
	         }

	         function u(t) {
	             t._.x = t.z + t.parent.m, t.m += t.parent.m
	         }

	         function a(t) {
	             t.x *= n, t.y = t.depth * e
	         }
	         return i.separation = function(n) {
	             return arguments.length ? (t = n, i) : t
	         }, i.size = function(t) {
	             return arguments.length ? (r = !1, n = +t[0], e = +t[1], i) : r ? null : [n, e]
	         }, i.nodeSize = function(t) {
	             return arguments.length ? (r = !0, n = +t[0], e = +t[1], i) : r ? [n, e] : null
	         }, i
	     }
	     bi.prototype = yi.prototype = {
	         constructor: bi,
	         count: function() {
	             return this.eachAfter(mi)
	         },
	         each: function(t) {
	             var n, e, r, i, o = this,
	                 u = [o];
	             do {
	                 for (n = u.reverse(), u = []; o = n.pop();)
	                     if (t(o), e = o.children)
	                         for (r = 0, i = e.length; r < i; ++r) u.push(e[r])
	             } while (u.length);
	             return this
	         },
	         eachAfter: function(t) {
	             for (var n, e, r, i = this, o = [i], u = []; i = o.pop();)
	                 if (u.push(i), n = i.children)
	                     for (e = 0, r = n.length; e < r; ++e) o.push(n[e]);
	             for (; i = u.pop();) t(i);
	             return this
	         },
	         eachBefore: function(t) {
	             for (var n, e, r = this, i = [r]; r = i.pop();)
	                 if (t(r), n = r.children)
	                     for (e = n.length - 1; e >= 0; --e) i.push(n[e]);
	             return this
	         },
	         sum: function(t) {
	             return this.eachAfter(function(n) {
	                 for (var e = +t(n.data) || 0, r = n.children, i = r && r.length; --i >= 0;) e += r[i].value;
	                 n.value = e
	             })
	         },
	         sort: function(t) {
	             return this.eachBefore(function(n) {
	                 n.children && n.children.sort(t)
	             })
	         },
	         path: function(t) {
	             for (var n = this, e = function(t, n) {
	                     if (t === n) return t;
	                     var e = t.ancestors(),
	                         r = n.ancestors(),
	                         i = null;
	                     for (t = e.pop(), n = r.pop(); t === n;) i = t, t = e.pop(), n = r.pop();
	                     return i
	                 }(n, t), r = [n]; n !== e;) n = n.parent, r.push(n);
	             for (var i = r.length; t !== e;) r.splice(i, 0, t), t = t.parent;
	             return r
	         },
	         ancestors: function() {
	             for (var t = this, n = [t]; t = t.parent;) n.push(t);
	             return n
	         },
	         descendants: function() {
	             var t = [];
	             return this.each(function(n) {
	                 t.push(n)
	             }), t
	         },
	         leaves: function() {
	             var t = [];
	             return this.eachBefore(function(n) {
	                 n.children || t.push(n)
	             }), t
	         },
	         links: function() {
	             var t = this,
	                 n = [];
	             return t.each(function(e) {
	                 e !== t && n.push({
	                     source: e.parent,
	                     target: e
	                 })
	             }), n
	         },
	         copy: function() {
	             return yi(this).eachBefore(wi)
	         }
	     }, zi.prototype = Object.create(bi.prototype);
	     Math.PI, Math.PI;

	     function Si(t) {
	         return t < 0 ? -1 : 1
	     }

	     function Ui(t, n, e) {
	         var r = t._x1 - t._x0,
	             i = n - t._x1,
	             o = (t._y1 - t._y0) / (r || i < 0 && -0),
	             u = (e - t._y1) / (i || r < 0 && -0),
	             a = (o * i + u * r) / (r + i);
	         return (Si(o) + Si(u)) * Math.min(Math.abs(o), Math.abs(u), .5 * Math.abs(a)) || 0
	     }

	     function Di(t, n) {
	         var e = t._x1 - t._x0;
	         return e ? (3 * (t._y1 - t._y0) / e - n) / 2 : n
	     }

	     function Ei(t, n, e) {
	         var r = t._x0,
	             i = t._y0,
	             o = t._x1,
	             u = t._y1,
	             a = (o - r) / 3;
	         t._context.bezierCurveTo(r + a, i + a * n, o - a, u - a * e, o, u)
	     }

	     function Yi(t) {
	         this._context = t
	     }

	     function Pi(t) {
	         this._context = t
	     }
	     Yi.prototype = {
	         areaStart: function() {
	             this._line = 0
	         },
	         areaEnd: function() {
	             this._line = NaN
	         },
	         lineStart: function() {
	             this._x0 = this._x1 = this._y0 = this._y1 = this._t0 = NaN, this._point = 0
	         },
	         lineEnd: function() {
	             switch (this._point) {
	                 case 2:
	                     this._context.lineTo(this._x1, this._y1);
	                     break;
	                 case 3:
	                     Ei(this, this._t0, Di(this, this._t0))
	             }(this._line || 0 !== this._line && 1 === this._point) && this._context.closePath(), this._line = 1 - this._line
	         },
	         point: function(t, n) {
	             var e = NaN;
	             if (n = +n, (t = +t) !== this._x1 || n !== this._y1) {
	                 switch (this._point) {
	                     case 0:
	                         this._point = 1, this._line ? this._context.lineTo(t, n) : this._context.moveTo(t, n);
	                         break;
	                     case 1:
	                         this._point = 2;
	                         break;
	                     case 2:
	                         this._point = 3, Ei(this, Di(this, e = Ui(this, t, n)), e);
	                         break;
	                     default:
	                         Ei(this, this._t0, e = Ui(this, t, n))
	                 }
	                 this._x0 = this._x1, this._x1 = t, this._y0 = this._y1, this._y1 = n, this._t0 = e
	             }
	         }
	     }, (function(t) {
	         this._context = new Pi(t)
	     }.prototype = Object.create(Yi.prototype)).point = function(t, n) {
	         Yi.prototype.point.call(this, n, t)
	     }, Pi.prototype = {
	         moveTo: function(t, n) {
	             this._context.moveTo(n, t)
	         },
	         closePath: function() {
	             this._context.closePath()
	         },
	         lineTo: function(t, n) {
	             this._context.lineTo(n, t)
	         },
	         bezierCurveTo: function(t, n, e, r, i, o) {
	             this._context.bezierCurveTo(n, t, r, e, o, i)
	         }
	     };
	     var Hi = {},
	         Fi = {},
	         ji = 34,
	         Bi = 10,
	         Li = 13;

	     function Xi(t) {
	         return new Function("d", "return {" + t.map(function(t, n) {
	             return JSON.stringify(t) + ": d[" + n + "]"
	         }).join(",") + "}")
	     }

	     function Ii(t) {
	         var n = Object.create(null),
	             e = [];
	         return t.forEach(function(t) {
	             for (var r in t) r in n || e.push(n[r] = r)
	         }), e
	     }

	     function $i(t, n) {
	         var e = t + "",
	             r = e.length;
	         return r < n ? new Array(n - r + 1).join(0) + e : e
	     }

	     function Oi(t) {
	         var n, e = t.getUTCHours(),
	             r = t.getUTCMinutes(),
	             i = t.getUTCSeconds(),
	             o = t.getUTCMilliseconds();
	         return isNaN(t) ? "Invalid Date" : ((n = t.getUTCFullYear()) < 0 ? "-" + $i(-n, 6) : n > 9999 ? "+" + $i(n, 6) : $i(n, 4)) + "-" + $i(t.getUTCMonth() + 1, 2) + "-" + $i(t.getUTCDate(), 2) + (o ? "T" + $i(e, 2) + ":" + $i(r, 2) + ":" + $i(i, 2) + "." + $i(o, 3) + "Z" : i ? "T" + $i(e, 2) + ":" + $i(r, 2) + ":" + $i(i, 2) + "Z" : r || e ? "T" + $i(e, 2) + ":" + $i(r, 2) + "Z" : "")
	     }

	     function qi(t) {
	         var n = new RegExp('["' + t + "\n\r]"),
	             e = t.charCodeAt(0);

	         function r(t, n) {
	             var r, i = [],
	                 o = t.length,
	                 u = 0,
	                 a = 0,
	                 s = o <= 0,
	                 c = !1;

	             function h() {
	                 if (s) return Fi;
	                 if (c) return c = !1, Hi;
	                 var n, r, i = u;
	                 if (t.charCodeAt(i) === ji) {
	                     for (; u++ < o && t.charCodeAt(u) !== ji || t.charCodeAt(++u) === ji;);
	                     return (n = u) >= o ? s = !0 : (r = t.charCodeAt(u++)) === Bi ? c = !0 : r === Li && (c = !0, t.charCodeAt(u) === Bi && ++u), t.slice(i + 1, n - 1).replace(/""/g, '"')
	                 }
	                 for (; u < o;) {
	                     if ((r = t.charCodeAt(n = u++)) === Bi) c = !0;
	                     else if (r === Li) c = !0, t.charCodeAt(u) === Bi && ++u;
	                     else if (r !== e) continue;
	                     return t.slice(i, n)
	                 }
	                 return s = !0, t.slice(i, o)
	             }
	             for (t.charCodeAt(o - 1) === Bi && --o, t.charCodeAt(o - 1) === Li && --o;
	                 (r = h()) !== Fi;) {
	                 for (var l = []; r !== Hi && r !== Fi;) l.push(r), r = h();
	                 n && null == (l = n(l, a++)) || i.push(l)
	             }
	             return i
	         }

	         function i(n, e) {
	             return n.map(function(n) {
	                 return e.map(function(t) {
	                     return u(n[t])
	                 }).join(t)
	             })
	         }

	         function o(n) {
	             return n.map(u).join(t)
	         }

	         function u(t) {
	             return null == t ? "" : t instanceof Date ? Oi(t) : n.test(t += "") ? '"' + t.replace(/"/g, '""') + '"' : t
	         }
	         return {
	             parse: function(t, n) {
	                 var e, i, o = r(t, function(t, r) {
	                     if (e) return e(t, r - 1);
	                     i = t, e = n ? function(t, n) {
	                         var e = Xi(t);
	                         return function(r, i) {
	                             return n(e(r), i, t)
	                         }
	                     }(t, n) : Xi(t)
	                 });
	                 return o.columns = i || [], o
	             },
	             parseRows: r,
	             format: function(n, e) {
	                 return null == e && (e = Ii(n)), [e.map(u).join(t)].concat(i(n, e)).join("\n")
	             },
	             formatBody: function(t, n) {
	                 return null == n && (n = Ii(t)), i(t, n).join("\n")
	             },
	             formatRows: function(t) {
	                 return t.map(o).join("\n")
	             }
	         }
	     }
	     var Vi = qi(","),
	         Ri = (Vi.parse, Vi.parseRows, Vi.format, Vi.formatBody, Vi.formatRows, qi("\t"));
	     Ri.parse, Ri.parseRows, Ri.format, Ri.formatBody, Ri.formatRows;
	     const Wi = "M",
	         Zi = "F";
	     class Qi {
	         constructor(t, n) {
	             this.nodeWidth = 200, this.nodeHeight = 0, this.separation = .5, this._options = n, this._nodes = null, this.init(t)
	         }
	         init(t) {
	             const n = ({
	                     children: t
	                 }) => 1 + (t ? Math.max(...t.map(n)) : 0),
	                 e = n(t);
	             let r = yi(t, t => this._options.showEmptyBoxes ? (!t.children && t.generation < e && (t.children = [this.createEmptyNode(t.generation + 1), this.createEmptyNode(t.generation + 1)]), t.children && t.children.length < 2 && (t.children[0].sex === Wi ? t.children.push(this.createEmptyNode(t.generation + 1)) : t.children.unshift(this.createEmptyNode(t.generation + 1))), t.children) : t.children);
	             const i = Ni().nodeSize([this.nodeWidth, this.nodeHeight]).separation(t => this.separation);
	             this._nodes = i(r)
	         }
	         get nodes() {
	             return this._nodes
	         }
	         createEmptyNode(t) {
	             return {
	                 id: 0,
	                 xref: "",
	                 sex: "",
	                 generation: t,
	                 color: this._options.defaultColor
	             }
	         }
	     }
	     class Ji {
	         constructor(t, n, e) {
	             this.boxWidth = 260,
				 this.boxHeight = 80,
				 this._config = t,
				 this._options = n,
				 this._hierarchy = e,
				 this.draw()
	         }
	         draw() {
	             let t = this._hierarchy.nodes.descendants(),
	                 n = this._hierarchy.nodes.links();
	             return t.forEach(function(t) {
	                 t.y = 300 * t.depth
	             }), this.drawLinks(n), this.drawNodes(t), this
	         }


			 //================  start modified  ================//
	         drawNodes(t) {
	             let n = this._config.visual.selectAll("g.person").data(t).enter()
				 .append("g")
				 .attr("class", "person")
				 .attr("transform", t => `translate(${t.y}, ${t.x})`);
	             n.filter(t => "" !== t.data.xref)
				 .append("title")
				 .text(t => t.data.name),
				 n.append("rect")
				 .attr("class", t => t.data.sex === Zi ? "female" : t.data.sex === Wi ? "male" : "")
				 .attr("rx", 0)
				 .attr("ry", 0)
				 .attr("x", -this.boxWidth / 2)
				 .attr("y", -this.boxHeight / 2)
				 .attr("width", this.boxWidth)
				 .attr("height", this.boxHeight)
				 .attr("fill-opacity", "0.5")
				 .attr("fill", t => t.data.color),
				 this.addImage(n),
				 n.filter(t => "" !== t.data.xref)
				 .append("text")
				 .attr("dx", -this.boxWidth / 2 + 80)
				 .attr("dy", "-20")
				 .attr("text-anchor", "start")
				 .attr("class", "name")
				 .text(t => t.data.name),
				 n.filter(t => "" !== t.data.xref)
				 .append("text")
				 .attr("dx", -this.boxWidth / 2 + 80)
				 .attr("dy", "0")
				 .attr("text-anchor", "start")
				 .attr("class", "lifespan")
				 .text(t => t.data.lifespan)
				  n.filter(t => "" !== t.data.xref)
				  .append("text")
				  .attr("dx", -this.boxWidth / 2 + 80)
				  .attr("dy", "16")
				  .attr("text-anchor", "start")
				  .attr("class", "bplace")
				  .text(t => t.data.bplace1)
				  n.filter(t => "" !== t.data.xref)
				  .append("text")
				  .attr("dx", -this.boxWidth / 2 + 80)
				  .attr("dy", "28")
				  .attr("text-anchor", "start")
				  .attr("class", "bplace")
				  .text(t => t.data.bplace2)
	         }

			 addImage(t) {
				t.append("svg:image")
					.attr("xlink:href",
					t => t.data.thumbnail ? t.data.thumbnail : t.data.sex === Zi ? theme + "images/silhouette_female.svg" : t.data.sex === Wi ? theme + "images/silhouette_male.svg" : theme + "images/silhouette_unknown.svg")
					.attr("x", -this.boxWidth / 2 + 5)
					.attr("y", -this.boxHeight / 2 + 5)
					.attr("height", 60)
					.attr("width", 60)
	         }
			 //================  end modified  ================//

	         drawLinks(t) {
	             this._config.visual.selectAll("path.link").data(t).enter().append("path").classed("link", !0).attr("d", t => this.elbow(t))
	         }
	         elbow(t) {
	             let n = t.source.x,
	                 e = t.source.y + this.boxWidth / 2,
	                 r = t.target.x,
	                 i = t.target.y - this.boxWidth / 2;
	             return "M" + this._options.direction * e + "," + n + "H" + this._options.direction * (e + (i - e) / 2) + "V" + r + "H" + this._options.direction * i
	         }
	     }
	     class Gi {
	         constructor() {
	             this._parent = null, this._svg = null, this._svgDefs = null, this._visual = null
	         }
	         get parent() {
	             return this._parent
	         }
	         set parent(t) {
	             this._parent = t
	         }
	         get svg() {
	             return this._svg
	         }
	         set svg(t) {
	             this._svg = t
	         }
	         get svgDefs() {
	             return this._svgDefs
	         }
	         set svgDefs(t) {
	             this._svgDefs = t
	         }
	         get visual() {
	             return this._visual
	         }
	         set visual(t) {
	             this._visual = t
	         }
	     }
	     class Ki {
	         constructor(t) {
	             this._overlay = t.parent.append("div").attr("class", "overlay").style("opacity", 1e-6)
	         }
	         show(t, n = 0, e = null) {
	             this._overlay.select("p").remove(), this._overlay.append("p").attr("class", "tooltip").text(t), this._overlay.transition().duration(n).style("opacity", 1).on("end", () => {
	                 "function" == typeof e && e()
	             })
	         }
	         hide(t = 0, n = 0) {
	             this._overlay.transition().delay(t).duration(n).style("opacity", 1e-6)
	         }
	     }
	     const to = .1,
	         no = 5;
	     class eo {
	         constructor(t) {
	             this._zoom = null, this._config = t, this.init()
	         }
	         init() {
	             let t = null;
	             this._zoom = vi().scaleExtent([to, no]).on("zoom", () => {
	                 D.sourceEvent && "touchmove" === D.sourceEvent.type && D.sourceEvent.touches.length < 2 || (t = D.transform.k, this._config.visual.attr("transform", D.transform))
	             }), this._zoom.filter(() => {
	                 if ("wheel" === D.type) {
	                     if (t && D.ctrlKey) {
	                         if (t <= to && D.deltaY > 0) return D.preventDefault(), !1;
	                         if (t >= no && D.deltaY < 0) return D.preventDefault(), !1
	                     }
	                     return D.ctrlKey
	                 }
	                 return !(!D.button && "touchmove" === D.type) || 2 === D.touches.length
	             })
	         }
	         get() {
	             return this._zoom
	         }
	     }
	     const ro = 500,
	         io = 10;
	     t.Options = class {
	         constructor(t, n, e = 3, r = "#eee", i = "#000", o = !1, u = !1, a = 1) {
	             this.data = null, this.generations = e, this.textPadding = 8, this.defaultColor = r, this.fontSize = 14, this.fontColor = i, this.individualUrl = t, this.showEmptyBoxes = u, this.rtl = o, this.labels = n, this.direction = a
	         }
	     }, t.Chart = class {
	         constructor(t, n) {
	             this._options = n, this._config = new Gi, this._overlay = null, this._zoom = null, this._config.parent = X(t), this.createSvg(), this.init()
	         }
	         createSvg() {
	             this._config.svg = this._config.parent.append("svg").attr("version", "1.1").attr("xmlns", "http://www.w3.org/2000/svg").attr("xmlns:xlink", "http://www.w3.org/1999/xlink").attr("width", "100%").attr("height", "100%").attr("text-rendering", "geometricPrecision").attr("text-anchor", "middle").on("contextmenu", () => D.preventDefault()).on("wheel", () => {
	                 D.ctrlKey || this.overlay.show(this._options.labels.zoom, 300, () => {
	                     this.overlay.hide(700, 800)
	                 })
	             }).on("touchend", () => {
	                 D.touches.length < 2 && this.overlay.hide(0, 800)
	             }).on("touchmove", () => {
	                 D.touches.length >= 2 ? this.overlay.hide() : this.overlay.show(this._options.labels.move)
	             }).on("click", () => this.doStopPropagation(), !0)
	         }
	         init() {
	             this._options.rtl && this._config.svg.classed("rtl", !0),
				 this.overlay = new Ki(this._config), X("#resetButton").on("click", () => this.doReset()),
				 this._config.visual = this._config.svg.append("g"),
				 this._zoom = new eo(this._config),
				 this._config.svg.call(this._zoom.get()),
				 this._config.svg.append("defs").attr("id", "imgdefs").append("clipPath").attr("id", "clip-circle").append("circle").attr("r", 35).attr("cx", -90).attr("cy", 0);
	             let t = new Qi(this._options.data, this._options);
	             new Ji(this._config, this._options, t), this.updateViewBox()
	         }
	         updateViewBox() {
	             let t = this._config.visual.node().getBBox(),
	                 n = this._config.parent.node().getBoundingClientRect(),
	                 e = Math.max(n.width, t.width),
	                 r = Math.max(n.height, t.height, ro),
	                 i = (e - t.width) / 2,
	                 o = (r - t.height) / 2,
	                 u = Math.ceil(t.x - i - io),
	                 a = Math.ceil(t.y - o - io);
	             e = Math.ceil(e + 2 * io), r = Math.ceil(r + 2 * io), this._config.svg.attr("viewBox", [u, a, e, r])
	         }
	         get overlay() {
	             return this._overlay
	         }
	         set overlay(t) {
	             this._overlay = t
	         }
	         doStopPropagation() {
	             D.defaultPrevented && D.stopPropagation()
	         }
	         doReset() {
	             this._config.svg.transition().duration(750).call(this._zoom.get().transform, ai)
	         }
	     }, Object.defineProperty(t, "__esModule", {
	         value: !0
	     })
	 });

</script>
