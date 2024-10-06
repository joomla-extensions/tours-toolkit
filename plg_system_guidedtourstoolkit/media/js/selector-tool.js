/**
 * css-selector-generator v3.6.8
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Riki Fridrich <riki@fczbkk.com> (https://github.com/fczbkk)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
!function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.CssSelectorGenerator=e():t.CssSelectorGenerator=e()}(self,(()=>(()=>{"use strict";var t={d:(e,n)=>{for(var o in n)t.o(n,o)&&!t.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:n[o]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e),r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},e={};function n(t){return"object"==typeof t&&null!==t&&t.nodeType===Node.ELEMENT_NODE}t.r(e),t.d(e,{default:()=>K,getCssSelector:()=>J});const o={NONE:"",DESCENDANT:" ",CHILD:" > "},r={id:"id",class:"class",tag:"tag",attribute:"attribute",nthchild:"nthchild",nthoftype:"nthoftype"},i="CssSelectorGenerator";function c(t="unknown problem",...e){console.warn(`${i}: ${t}`,...e)}const u={selectors:[r.id,r.class,r.tag,r.attribute],includeTag:!1,whitelist:[],blacklist:[],combineWithinSelector:!0,combineBetweenSelectors:!0,root:null,maxCombinations:Number.POSITIVE_INFINITY,maxCandidates:Number.POSITIVE_INFINITY};function s(t){return t instanceof RegExp}function a(t){return["string","function"].includes(typeof t)||s(t)}function l(t){return Array.isArray(t)?t.filter(a):[]}function f(t){const e=[Node.DOCUMENT_NODE,Node.DOCUMENT_FRAGMENT_NODE,Node.ELEMENT_NODE];return function(t){return t instanceof Node}(t)&&e.includes(t.nodeType)}function d(t,e){if(f(t))return t.contains(e)||c("element root mismatch","Provided root does not contain the element. This will most likely result in producing a fallback selector using element's real root node. If you plan to use the selector using provided root (e.g. `root.querySelector`), it will nto work as intended."),t;const n=e.getRootNode({composed:!1});return f(n)?(n!==document&&c("shadow root inferred","You did not provide a root and the element is a child of Shadow DOM. This will produce a selector using ShadowRoot as a root. If you plan to use the selector using document as a root (e.g. `document.querySelector`), it will not work as intended."),n):e.ownerDocument.querySelector(":root")}function m(t){return"number"==typeof t?t:Number.POSITIVE_INFINITY}function p(t=[]){const[e=[],...n]=t;return 0===n.length?e:n.reduce(((t,e)=>t.filter((t=>e.includes(t)))),e)}function h(t){return[].concat(...t)}function g(t){const e=t.map((t=>{if(s(t))return e=>t.test(e);if("function"==typeof t)return e=>{const n=t(e);return"boolean"!=typeof n?(c("pattern matcher function invalid","Provided pattern matching function does not return boolean. It's result will be ignored.",t),!1):n};if("string"==typeof t){const e=new RegExp("^"+t.replace(/[|\\{}()[\]^$+?.]/g,"\\$&").replace(/\*/g,".+")+"$");return t=>e.test(t)}return c("pattern matcher invalid","Pattern matching only accepts strings, regular expressions and/or functions. This item is invalid and will be ignored.",t),()=>!1}));return t=>e.some((e=>e(t)))}function y(t,e,n){const o=Array.from(d(n,t[0]).querySelectorAll(e));return o.length===t.length&&t.every((t=>o.includes(t)))}function b(t,e){e=null!=e?e:function(t){return t.ownerDocument.querySelector(":root")}(t);const o=[];let r=t;for(;n(r)&&r!==e;)o.push(r),r=r.parentElement;return o}function N(t,e){return p(t.map((t=>b(t,e))))}const S=", ",E=new RegExp(["^$","\\s"].join("|")),w=new RegExp(["^$"].join("|")),I=[r.nthoftype,r.tag,r.id,r.class,r.attribute,r.nthchild],v=g(["class","id","ng-*"]);function T({name:t}){return`[${t}]`}function C({name:t,value:e}){return`[${t}='${e}']`}function O({nodeName:t,nodeValue:e}){return{name:V(t),value:V(e)}}function x(t){const e=Array.from(t.attributes).filter((e=>function({nodeName:t},e){const n=e.tagName.toLowerCase();return!(["input","option"].includes(n)&&"value"===t||v(t))}(e,t))).map(O);return[...e.map(T),...e.map(C)]}function j(t){return(t.getAttribute("class")||"").trim().split(/\s+/).filter((t=>!w.test(t))).map((t=>`.${V(t)}`))}function A(t){const e=t.getAttribute("id")||"",n=`#${V(e)}`,o=t.getRootNode({composed:!1});return!E.test(e)&&y([t],n,o)?[n]:[]}function $(t){const e=t.parentNode;if(e){const o=Array.from(e.childNodes).filter(n).indexOf(t);if(o>-1)return[`:nth-child(${o+1})`]}return[]}function D(t){return[V(t.tagName.toLowerCase())]}function R(t){const e=[...new Set(h(t.map(D)))];return 0===e.length||e.length>1?[]:[e[0]]}function P(t){const e=R([t])[0],n=t.parentElement;if(n){const o=Array.from(n.children).filter((t=>t.tagName.toLowerCase()===e)),r=o.indexOf(t);if(r>-1)return[`${e}:nth-of-type(${r+1})`]}return[]}function _(t=[],{maxResults:e=Number.POSITIVE_INFINITY}={}){return Array.from(function*(t=[],{maxResults:e=Number.POSITIVE_INFINITY}={}){let n=0,o=L(1);for(;o.length<=t.length&&n<e;){n+=1;const e=o.map((e=>t[e]));yield e,o=k(o,t.length-1)}}(t,{maxResults:e}))}function k(t=[],e=0){const n=t.length;if(0===n)return[];const o=[...t];o[n-1]+=1;for(let t=n-1;t>=0;t--)if(o[t]>e){if(0===t)return L(n+1);o[t-1]++,o[t]=o[t-1]+1}return o[n-1]>e?L(n+1):o}function L(t=1){return Array.from(Array(t).keys())}const M=":".charCodeAt(0).toString(16).toUpperCase(),F=/[ !"#$%&'()\[\]{|}<>*+,./;=?@^`~\\]/;function V(t=""){var e,n;return null!==(n=null===(e=null===CSS||void 0===CSS?void 0:CSS.escape)||void 0===e?void 0:e.call(CSS,t))&&void 0!==n?n:function(t=""){return t.split("").map((t=>":"===t?`\\${M} `:F.test(t)?`\\${t}`:escape(t).replace(/%/g,"\\"))).join("")}(t)}const Y={tag:R,id:function(t){return 0===t.length||t.length>1?[]:A(t[0])},class:function(t){return p(t.map(j))},attribute:function(t){return p(t.map(x))},nthchild:function(t){return p(t.map($))},nthoftype:function(t){return p(t.map(P))}},q={tag:D,id:A,class:j,attribute:x,nthchild:$,nthoftype:P};function B(t){return t.includes(r.tag)||t.includes(r.nthoftype)?[...t]:[...t,r.tag]}function G(t={}){const e=[...I];return t[r.tag]&&t[r.nthoftype]&&e.splice(e.indexOf(r.tag),1),e.map((e=>{return(o=t)[n=e]?o[n].join(""):"";var n,o})).join("")}function H(t,e,n="",r){const i=function(t,e){return""===e?t:function(t,e){return[...t.map((t=>e+o.DESCENDANT+t)),...t.map((t=>e+o.CHILD+t))]}(t,e)}(function(t,e,n){const o=function(t,e){const{blacklist:n,whitelist:o,combineWithinSelector:r,maxCombinations:i}=e,c=g(n),u=g(o);return function(t){const{selectors:e,includeTag:n}=t,o=[].concat(e);return n&&!o.includes("tag")&&o.push("tag"),o}(e).reduce(((e,n)=>{const o=function(t,e){var n;return(null!==(n=Y[e])&&void 0!==n?n:()=>[])(t)}(t,n),s=function(t=[],e,n){return t.filter((t=>n(t)||!e(t)))}(o,c,u),a=function(t=[],e){return t.sort(((t,n)=>{const o=e(t),r=e(n);return o&&!r?-1:!o&&r?1:0}))}(s,u);return e[n]=r?_(a,{maxResults:i}):a.map((t=>[t])),e}),{})}(t,n),r=function(t,e){return function(t){const{selectors:e,combineBetweenSelectors:n,includeTag:o,maxCandidates:r}=t,i=n?_(e,{maxResults:r}):e.map((t=>[t]));return o?i.map(B):i}(e).map((e=>function(t,e){const n={};return t.forEach((t=>{const o=e[t];o.length>0&&(n[t]=o)})),function(t={}){let e=[];return Object.entries(t).forEach((([t,n])=>{e=n.flatMap((n=>0===e.length?[{[t]:n}]:e.map((e=>Object.assign(Object.assign({},e),{[t]:n})))))})),e}(n).map(G)}(e,t))).filter((t=>t.length>0))}(o,n),i=h(r);return[...new Set(i)]}(t,r.root,r),n);for(const e of i)if(y(t,e,r.root))return e;return null}function W(t){return{value:t,include:!1}}function U({selectors:t,operator:e}){let n=[...I];t[r.tag]&&t[r.nthoftype]&&(n=n.filter((t=>t!==r.tag)));let o="";return n.forEach((e=>{(t[e]||[]).forEach((({value:t,include:e})=>{e&&(o+=t)}))})),e+o}function z(t){return[":root",...b(t).reverse().map((t=>{const e=function(t,e,n=o.NONE){const r={};return e.forEach((e=>{Reflect.set(r,e,function(t,e){return q[e](t)}(t,e).map(W))})),{element:t,operator:n,selectors:r}}(t,[r.nthchild],o.CHILD);return e.selectors.nthchild.forEach((t=>{t.include=!0})),e})).map(U)].join("")}function J(t,e={}){const o=function(t){(t instanceof NodeList||t instanceof HTMLCollection)&&(t=Array.from(t));const e=(Array.isArray(t)?t:[t]).filter(n);return[...new Set(e)]}(t),i=function(t,e={}){const n=Object.assign(Object.assign({},u),e);return{selectors:(o=n.selectors,Array.isArray(o)?o.filter((t=>{return e=r,n=t,Object.values(e).includes(n);var e,n})):[]),whitelist:l(n.whitelist),blacklist:l(n.blacklist),root:d(n.root,t),combineWithinSelector:!!n.combineWithinSelector,combineBetweenSelectors:!!n.combineBetweenSelectors,includeTag:!!n.includeTag,maxCombinations:m(n.maxCombinations),maxCandidates:m(n.maxCandidates)};var o}(o[0],e);let c="",s=i.root;function a(){return function(t,e,n="",o){if(0===t.length)return null;const r=[t.length>1?t:[],...N(t,e).map((t=>[t]))];for(const t of r){const e=H(t,0,n,o);if(e)return{foundElements:t,selector:e}}return null}(o,s,c,i)}let f=a();for(;f;){const{foundElements:t,selector:e}=f;if(y(o,e,i.root))return e;s=t[0],c=e,f=a()}return o.length>1?o.map((t=>J(t,i))).join(S):function(t){return t.map(z).join(S)}(o)}const K=J;return e})()));

/**
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

document.addEventListener("readystatechange", function(event) {

	let toggle = false;

	// Clear any pre-existing elements to avoid conflicts
	document.querySelectorAll('#toolkitBar').forEach(e => e.remove());

	let toolkitBar = document.getElementById('toolkitBar');
	if (!toolkitBar) {
		toolkitBar = document.createElement('div');
		toolkitBar.id = 'toolkitBar';
		toolkitBar.setAttribute('data-no-display', 'true');

		// Add a button to toggle the state
		let toggleButton = document.createElement('button');

		const toggleText = document.createElement('span');
		toggleText.setAttribute('data-no-display', 'true');
		toggleText.classList.add('me-3');
		toggleText.textContent = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_ONOFF');

		toggleButton.appendChild(toggleText);

		const toggleIcon = document.createElement('span');
		toggleIcon.setAttribute('data-no-display', 'true');
		toggleIcon.setAttribute('aria-hidden', 'true');
		toggleIcon.classList.add('fas', 'fa-toggle-on');
		toggleIcon.title = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_OFF');

		toggleButton.appendChild(toggleIcon);

		toggleButton.id = 'toggleButton';
		toggleButton.classList.add('btn');
		toggleButton.setAttribute('data-no-display', 'true');
		toggleButton.addEventListener('click', () => {
		    toggle = !toggle;
			if (toggle) {
				toggleIcon.classList.remove('fa-toggle-on');
				toggleIcon.classList.add('fa-toggle-off');
				toggleIcon.title = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_ON');
				toggleText.textContent = '';
				toggleText.classList.remove('me-3');
			} else {
				toggleIcon.classList.remove('fa-toggle-off');
				toggleIcon.classList.add('fa-toggle-on');
				toggleIcon.title = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_OFF');
				toggleText.textContent = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_ONOFF');
				toggleText.classList.add('me-3');
			}
		    console.log(`Selector generation toggled ${toggle ? 'on' : 'off'}.`);
		});
		toolkitBar.appendChild(toggleButton);

		document.body.appendChild(toolkitBar);
	}

	function getSelector(element) {
		// get unique CSS selector for that element
		let selector = CssSelectorGenerator.getCssSelector(element, {
			selectors: ['id', 'class', 'attribute', 'tag'],
			blacklist: [".toolkit-highlight", "[aria-label]", "[placeholder]", "[aria-expanded]"],
			whitelist: ["[href]"],
			includeTag: true,
		});

		return selector;
	}

	function getFixedSelector(selector) {
		console.log("selector", selector);

		selector = selector.replace(/\[role]/g, ''); // useless [role]
		selector = selector.replace(/\'/g, '\"'); // change ' to "
		selector = selector.replace(/\\/g, ''); // remove /
		selector = selector.replace(`${Joomla.getOptions('system.paths').rootFull}`, '');
		selector = selector.replace(/(a\[href[^?]*\?)/, 'a[href*=\"'); // cleanup href
		selector = selector.replace(/\[src=/, '[src*='); // cleanup src
		selector = selector.indexOf('#') !== -1 ? '#' + selector.split('#')[1] : selector; // no need to have a tag before the id

		const count = (selector.match(/nth-child/g) || []).length; // too many nth-child
		if (count > 5) {
			selector = '';
		}

		if (selector.indexOf('style=') !== -1) { // cannot target through styles
			selector = '';
		}

		let explanation = [];

		if (selector.indexOf('aria-label') !== -1 || selector.indexOf('placeholder') !== -1) {
			explanation.push(Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_ONLY_IN_MONOLINGUAL'));
		}

		if (selector.indexOf('href*=') !== -1) {
			explanation.push(Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_HREF'));
		}

		if (selector.indexOf('choices__inner') !== -1) {
			explanation.push(Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_FANCYSELECT'));
		}

		if (selector.match(/\d+/)) {
			explanation.push(Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_EXPLAIN_NUMBERS'));
		}

		return [selector, explanation.join('<br>')];
	}

	function toggleSelectorGeneration(element) {

		if (toggle) {

			let selectorDisplay = document.getElementById('selectorDisplay');
		    if (!selectorDisplay) {
				selectorDisplay = document.createElement('div');
				selectorDisplay.id = 'selectorDisplay';
				selectorDisplay.setAttribute('data-no-display', 'true');

				const selectorLayer = document.createElement('div');
				selectorLayer.id = 'selectorLayer';
				selectorLayer.setAttribute('data-no-display', 'true');

				const selectorText = document.createElement('span');
				selectorText.id = 'selectorText';
				selectorText.setAttribute('data-no-display', 'true');
				selectorText.textContent = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_SELECT_ELEMENT');

				selectorLayer.appendChild(selectorText);

				const selectorExplanation = document.createElement('span');
				selectorExplanation.id = 'selectorExplanation';
				selectorExplanation.setAttribute('data-no-display', 'true');

				selectorLayer.appendChild(selectorExplanation);

				selectorDisplay.appendChild(selectorLayer);

				const copyButton = document.createElement('button');
				copyButton.id = 'copyButton';
				copyButton.title = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_COPY');
				copyButton.classList.add('btn');
				copyButton.setAttribute('data-no-display', 'true');
				copyButton.setAttribute('disabled', 'disabled');

				const copyIcon = document.createElement('span');
				copyIcon.classList.add('fas', 'fa-copy');
				copyIcon.setAttribute('data-no-display', 'true');
				copyIcon.setAttribute('aria-hidden', 'true');
				copyButton.appendChild(copyIcon);

				selectorDisplay.appendChild(copyButton);

				const successLayer = document.createElement('div');
				successLayer.id = 'successLayer';
				//successLayer.setAttribute('title', Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_COPIED'));
				successLayer.setAttribute('data-no-display', 'true');

				const successIcon = document.createElement('span');
				successIcon.classList.add('fas', 'fa-circle-check');
				successIcon.setAttribute('data-no-display', 'true');
				successIcon.setAttribute('aria-hidden', 'true');
				successLayer.appendChild(successIcon);

				selectorDisplay.appendChild(successLayer);

				toolkitBar.appendChild(selectorDisplay);
		    }

			// Copy the selector to clipboard
			const copyButton = document.getElementById('copyButton');
			copyButton.onclick = function() {
			    navigator.clipboard.writeText(document.getElementById('selectorText').textContent).then(() => {
			        console.log('Selector copied to clipboard');
					successLayer.classList.remove('hide');
					successLayer.classList.add('show');
			        setTimeout(() => {
			            // Hide the icon after 2 seconds
						successLayer.classList.remove('show');
						successLayer.classList.add('hide');
			        }, 2000);
			    }).catch(err => {
			        console.error('Failed to copy: ', err);
			    });
			};

			// Display the selector
			if (!element.hasAttribute('data-no-display')) {
				let selector = getSelector(element);
				let fixedSelector = getFixedSelector(selector);
				const selectorText = document.getElementById('selectorText');
				const selectorExplanation = document.getElementById('selectorExplanation');

				selectorExplanation.innerHTML = fixedSelector[1];

				if (fixedSelector[1]) {
					document.getElementById('selectorLayer').classList.add('py-2');
				} else {
					document.getElementById('selectorLayer').classList.remove('py-2');
				}

				if (fixedSelector[0]) {
					copyButton.removeAttribute('disabled');
					selectorText.textContent = fixedSelector[0];
				} else {
					copyButton.setAttribute('disabled', 'disabled');
					selectorText.textContent = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURSTOOLKIT_SELECTORTOOL_UNUSABLE_SELECTOR');
				}
			}
		} else {
			const selectorDisplay = document.getElementById('selectorDisplay');
			if (selectorDisplay) {
				toolkitBar.removeChild(selectorDisplay);
			}
		}
	}

	document.body.addEventListener("mouseover", function (event) {
		const element = event.target;
		if (toggle && !element.hasAttribute('data-no-display')) {
			element.classList.add('toolkit-highlight');
		}
	});

	document.body.addEventListener("mouseout", function (event) {
		const element = event.target;
		if (toggle && !element.hasAttribute('data-no-display')) {
			element.classList.remove('toolkit-highlight');
		}
	});

	// track every click
	document.body.addEventListener("click", function (event) {

	  	// get reference to the element user clicked on
	  	const element = event.target;

		if (toggle) {
			// Prevent the default action, like navigating to a new page
			event.preventDefault();

			// Stop further propagation of the click event
			event.stopPropagation();
		}

		// Call the toggle function
  		toggleSelectorGeneration(element);
	});
});
