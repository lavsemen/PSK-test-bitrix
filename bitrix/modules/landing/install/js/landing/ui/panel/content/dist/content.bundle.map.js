{"version":3,"sources":["content.bundle.js"],"names":["this","BX","Landing","UI","exports","landing_ui_panel_base","main_core","getDeltaFromEvent","event","deltaX","deltaY","Type","isUndefined","wheelDeltaX","wheelDeltaY","deltaMode","Number","isNaN","wheelDelta","x","y","calculateDurationTransition","diff","defaultDuration","Math","min","scrollTo","container","element","Promise","resolve","elementTop","duration","defaultMargin","elementMarginTop","max","parseInt","Dom","style","containerScrollTop","scrollTop","HTMLIFrameElement","offsetTop","contentWindow","scrollY","pos","top","abs","start","finish","easing","step","state","animate","setTimeout","_templateObject8","data","babelHelpers","taggedTemplateLiteral","_templateObject7","_templateObject6","_templateObject5","_templateObject4","_templateObject3","_templateObject2","_templateObject","Content","_BasePanel","inherits","createClass","key","value","createOverlay","Tag","render","createHeader","createTitle","createBody","createSidebar","createContent","createFooter","calculateTransitionDuration","arguments","length","undefined","scrollTo$$1","getDeltaFromEvent$$1","id","_this","classCallCheck","possibleConstructorReturn","getPrototypeOf","call","defineProperty","assertThisInitialized","addClass","layout","Object","freeze","overlay","header","title","body","footer","sidebar","content","closeButton","Button","BaseButton","className","onClick","hide","emit","attrs","Loc","getMessage","forms","Collection","FormCollection","buttons","ButtonCollection","sidebarButtons","wheelEventName","isNil","window","onwheel","onmousewheel","onMouseWheel","bind","onMouseEnter","onMouseLeave","removeClass","append","isString","concat","subTitle","showFromRight","setLayoutClass","init","Event","onKeyDown","PageObject","getEditorWindow","scrollAnimation","scrollObserver","IntersectionObserver","onIntersecting","_this2","document","setTitle","isArray","forEach","item","appendFooterButton","isDomNode","items","isIntersecting","target","keyCode","stopPropagation","contains","right","scrollTarget","currentTarget","unbind","_this3","preventDefault","delta","requestAnimationFrame","isShown","shouldAdjustActionsPanels","adjustActionsPanels","show","options","_this4","Utils","Show","then","_this5","Hide","appendForm","form","add","getNode","appendCard","card","observe","clear","clearContent","clearSidebar","clean","innerHTML","button","appendSidebarButton","setOverlayClass","renderTo","get","prototype","BasePanel","Panel"],"mappings":"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","file":"content.bundle.map.js"}