{"version":3,"sources":["script.js"],"names":["this","BX","Socialnetwork","exports","main_core_events","main_popup","main_core","Toolbar","params","_this","babelHelpers","classCallCheck","id","menuItems","componentName","Type","isStringFilled","menuButtonId","menuButton","document","getElementById","addEventListener","e","menuButtonClick","currentTarget","createClass","key","value","getId","_id","getSetting","name","defaultval","_settings","getParam","bindNode","openMenu","menuOpened","closeMenu","isArray","forEach","item","isUndefined","SEPARATOR","push","TYPE","text","TITLE","onclick","LINK","concat","menuId","toLowerCase","Popup","show","autoHide","closeByEsc","offsetTop","offsetLeft","events","onPopupShow","bind","onPopupClose","onPopupDestroy","menuPopup","MenuManager","currentItem","popupWindow","destroy","Data","ActionManager","signedParameters","gridId","viewProfile","userId","parseInt","pathToUser","SidePanel","Instance","open","replace","cacheable","allowChangeHistory","contentClassName","loader","width","act","action","ajax","runComponentAction","mode","data","fields","then","response","success","Main","gridManager","reload","disconnectDepartment","_this2","Manager","getById","repo","get","filterId","gridContainer","gridContainerId","toolbar","toolbarInstance","actionManagerInstance","EventEmitter","subscribe","event","_event$getCompatData","getCompatData","_event$getCompatData2","slicedToArray","sliderEvent","getEventId","code","set","getActionManager","defineProperty","Map","WorkgroupUserList","Event"],"mappings":"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","file":"script.map.js"}