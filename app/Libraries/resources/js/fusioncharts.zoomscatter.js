!function(e){"object"==typeof module&&"undefined"!=typeof module.exports?module.exports=e:e()}((function(){(window.webpackJsonpFusionCharts=window.webpackJsonpFusionCharts||[]).push([[9],{1146:function(e,t,o){"use strict";var i=o(178);t.__esModule=!0,t["default"]=void 0;var a=i(o(1147));t.ZoomScatter=a["default"];var n={name:"zoomscatter",type:"package",requiresFusionCharts:!0,extension:function(e){e.addDep(a["default"])}};t["default"]=n},1147:function(e,t,o){"use strict";var i=o(178);t.__esModule=!0,t["default"]=void 0;var a=i(o(1148))["default"];t["default"]=a},1148:function(e,t,o){"use strict";var i=o(178);t.__esModule=!0,t["default"]=void 0;var a=i(o(198)),n=i(o(596)),r=i(o(1149)),l=o(184),s=o(1117),h=o(192),c=o(196),g=(0,h.getDep)("redraphael","plugin");g.addSymbol(s.symbolList);var d=function(e){function t(){var t;return(t=e.call(this)||this).highlightEnabled=!1,t.isXY=!0,t.zoom=!0,t.zoomX=!0,t.zoomY=!0,t.defaultZeroPlaneHighlighted=!1,t}(0,a["default"])(t,e),t.getName=function(){return"ZoomScatter"};var o=t.prototype;return o.getName=function(){return"ZoomScatter"},o.__setDefaultConfig=function(){e.prototype.__setDefaultConfig.call(this);var t=this.config;t.friendlyName="ZoomScatter Chart",t.defaultDatasetType="zoomscatter",t.enablemousetracking=!0,t.animation=0},o.configureAttributes=function(t){var o,i=this.config;e.prototype.configureAttributes.call(this,t),o=this.getFromEnv("dataSource").chart,i.stepZoom=400/(100-(0,l.pluckNumber)(o.stepzoom,25)),i.stepZoom<=2&&(i.stepZoom=1.9),i.showToolBarButtonTooltext=(0,l.pluckNumber)(o.showtoolbarbuttontooltext,1),i.btnResetChartToolText=i.showToolBarButtonTooltext?(0,l.pluck)(o.btnresetcharttooltext,"Reset Chart"):"",i.btnZoomOutToolText=i.showToolBarButtonTooltext?(0,l.pluck)(o.btnzoomouttooltext,"Zoom out to previous level"):"",i.btnZoomInToolText=i.showToolBarButtonTooltext?(0,l.pluck)(o.btnzoomintooltext,"<strong>Zoom in</strong><br/>Or double-click on plot to zoom-in"):"",i.btnSelectZoomToolText=i.showToolBarButtonTooltext?(0,l.pluck)(o.btnselectzoomtooltext,"<strong>Select a region to zoom-in</strong><br/>Click to enable pan mode."):"",i.btnPanToolText=i.showToolBarButtonTooltext?(0,l.pluck)(o.btnpantooltext,"<strong>Drag to move across chart</strong><br/>Click to enable select-zoom mode."):""},t.includeInputOptions=function(){return["DragPan","DragZoomIn","ZoomResetButton","ZoomOutButton","ZoomInButton","DbTapZoom","PinchZoom"]},o.getInputConfigurations=function(){var e=this,t=e.config,o=function(){e.addJob("spaceManage",(function(){e._manageInteractiveSpace()}),c.priorityList.configure)};return{dragZoomIn:{scaleX:!0,scaleY:!0,boxStyle:{"stroke-width":1,stroke:"red",fill:"#00FF00",opacity:.2,cursor:"ne-resize"},dragendFn:o,tooltext:t.btnSelectZoomToolText,zoomDecimalLimit:2},zoomResetButton:{tooltext:t.btnResetChartToolText,hookFn:o},zoomOutButton:{tooltext:t.btnZoomOutToolText,hookFn:o},zoomInButton:{tooltext:t.btnZoomInToolText,stepzoom:t.stepZoom,zoomDecimalLimit:2,hookFn:o},dragPan:{tooltext:t.btnPanToolText},dbTapZoom:{stepzoom:t.stepZoom,zoomDecimalLimit:2,hookFn:o},pinchZoom:{zoomDecimalLimit:2}}},o._checkInvalidSpecificData=function(){if(!this.getFromEnv("dataSource").dataset)return!0},o.getDatasets=function(){var e=[];return this.iterateComponents((function(t){t.getType&&"dataset"===t.getType()&&e.push(t)})),e},o.getDSdef=function(){return r["default"]},o.getDSGroupdef=function(){},t}(n["default"]);d.prototype._manageInteractiveSpace=l._manageInteractiveSpace;var u=d;t["default"]=u},1149:function(e,t,o){"use strict";var i=o(178);t.__esModule=!0,t["default"]=void 0;var a=i(o(198)),n=i(o(600)),r=i(o(601)),l=o(196),s=o(184),h=i(o(285)),c=window,g=function(){this.hide()},d=Math.PI,u=s.preDefStr.DEFAULT,m=2*d,f=function(e,t){return e*t>=0},p=function(e,t,o,i,a,n,r,l){var s,h,c,g,d,u,m,p,x,v;return v=(s=i-t)*r+(c=e-o)*l+(d=o*t-e*i),0!==(x=s*a+c*n+d)&&0!==v&&f(x,v)?0:(p=(h=l-n)*o+(g=a-r)*i+(u=r*n-a*l),0!==(m=h*e+g*t+u)&&0!==p&&f(m,p)?0:(s*g-h*c,1))},x=function(e,t,o){if(!(isNaN(e.x)||isNaN(e.y)||isNaN(t.x)||isNaN(t.y)))return p(e.x,e.y,t.x,t.y,o.xMinWPad,o.yMaxWPad,o.xMaxWPad,o.yMaxWPad)||p(e.x,e.y,t.x,t.y,o.xMaxWPad,o.yMaxWPad,o.xMaxWPad,o.yMinWPad)||p(e.x,e.y,t.x,t.y,o.xMaxWPad,o.yMinWPad,o.xMinWPad,o.yMinWPad)},v=function(e){var t=(e.config.axisRange.max-e.config.axisRange.min)/(e.getVisibleConfig().maxValue-e.getVisibleConfig().minValue);return t=Math.round(1e3*t)/1e3},y=function(e){return[e-1,e,e+1]},b=function(e,t,o){return e>=t&&e<=o},P=function(e,t,o){return b(e,t,o)||b(e,o,t)},S=function(e,t){var o=t,i=e;return(o=parseFloat(o/100))<0?o=0:o>1&&(o=1),i||(i="#FFFFFF"),s.isIE&&!s.hasSVG?o?i:"transparent":(i=i.replace(/^#?([a-f0-9]+)/gi,"$1"),(i=(0,s.HEXtoRGB)(i))[3]=o.toString(),"rgba("+i.join(",")+")")},T=function(e){var t,o,i=[],a=e.getVisibleConfig(),n=a.maxValue-a.minValue,r=a.minValue+n/2,l=e.config,s=l.axisRange;return t=Math.abs((r-(l.isReverse?s.max:s.min))/n),o=i.focusedGrid=Math.floor(t),i.push(o),t%1>.5?i.push(o+1):t%1<.5&&i.unshift(o-1),i},k=function(e){function t(){return e.apply(this,arguments)||this}(0,a["default"])(t,e);var o=t.prototype;return o.getType=function(){return"dataset"},o.getName=function(){return"zoomScatter"},o.configureAttributes=function(t){e.prototype.configureAttributes.call(this,t);var o,i,a,n,r,l,h,c=this.config,g=this.getFromEnv("chart"),d=g.config,u=g.getFromEnv("dataSource").chart,m=(0,s.pluck)(t.anchorbordercolor,u.anchorbordercolor),f=(0,s.getFirstColor)((0,s.pluck)(m,c.plotBorderColor)),p=(0,s.pluckNumber)(t.anchorborderthickness,u.anchorborderthickness,m?1:0),x=(0,s.getFirstColor)((0,s.pluck)(t.anchorbgcolor,t.color,u.anchorbgcolor,c.plotColor)),v=(0,s.pluck)(t.anchoralpha,t.alpha,u.anchoralpha,s.HUNDREDSTRING),y=(0,s.pluck)(t.anchorbgalpha,t.alpha,u.anchorbgalpha,s.HUNDREDSTRING),b={color:c.lineColor,alpha:c.lineAlpha};c.plotCosmetics={fillStyle:S(x,v*y/100),strokeStyle:S(f,v),borderWidth:p,lineWidth:c.lineThickness,lineStrokeStyle:(0,s.toRaphaelColor)(b)},this.config.JSONData=t,c.anchorBorderThickness=(0,s.pluckNumber)(t.anchorborderthickness,u.anchorborderthickness,m?1:0),c.chunkSize=Math.floor(Math.min((t.data||[]).length/5,5e4)),h=c.staticRadius=(0,s.pluckNumber)(u.staticradius,0),c.radius=(0,s.pluckNumber)(t.radius,t.anchorradius,u.radius,u.anchorradius,h?3:.5),l=c.showHoverEffect,o=(0,s.getFirstColor)((0,s.pluck)(t.plotfillhovercolor,t.hovercolor,u.plotfillhovercolor,u.hovercolor,c.anchorbgcolor)),i=(0,s.pluck)(t.plotfillhoveralpha,t.hoveralpha,u.plotfillhoveralpha,u.hoveralpha,s.HUNDREDSTRING),a=(0,s.getFirstColor)((0,s.pluck)(t.plotfillhovercolor,t.hovercolor,u.plotfillhovercolor,u.hovercolor,o)),r=(0,s.pluck)(t.plotfillhoveralpha,t.hoveralpha,u.plotfillhoveralpha,u.hoveralpha,s.HUNDREDSTRING),n=(0,s.pluckNumber)(t.borderhoverthickness,u.borderhoverthickness,1),c.hoverCosmetics={showHoverEffect:l,fill:S(o,i),borderColor:S(a,r),borderThickness:n,plotFillHoverColor:o,plotFillHoverAlpha:i,borderHoverColor:a,borderHoverAlpha:r},c.tooltip={toolTipVisible:d.showtooltip,seriesNameInToolTip:d.seriesnameintooltip,toolTipSepChar:d.tooltipsepchar},c.lastViewPort={},this.disableScrollBars(),this.setState("dirty",!0)},o.hasDrawingRefChanged=function(){var e,t=this.getFromEnv("xAxis"),o=this.getFromEnv("yAxis"),i=this.config,a=i.axisConfig=i.axisConfig||{},n=v(t),r=this.getFromEnv("chartConfig"),l=v(o);return e=a.xZoomScale!==n||a.yZoomScale!==l||i.prevCanvasHeight!==r.canvasHeight||i.prevCanvasWidth!==r.canvasWidth,a.xZoomScale=n,a.yZoomScale=l,i.prevCanvasHeight=r.canvasHeight,i.prevCanvasWidth=r.canvasWidth,e},o.saveScrollPos=function(){var e=this.getFromEnv("xAxis"),t=this.getFromEnv("yAxis"),o=this.config,i=o.axisConfig=o.axisConfig||{};i.xScrollPos=e.config.apparentScrollPos,i.yScrollPos=t.config.apparentScrollPos},o.disableScrollBars=function(){var e=this.getFromEnv("xAxis"),t=this.getFromEnv("yAxis");e.setScrollType("none"),t.setScrollType("none")},o.calculateZoomedRadius=function(){var e=this.config,t=this.getFromEnv("chart").config,o=e.axisConfig;e.zoomedRadius=Math.min(e.staticRadius?e.radius:e.radius*Math.min(o.xZoomScale,o.yZoomScale),t.canvasWidth/2,t.canvasHeight/2)},o.setupKdTree=function(){var e,t,o,i=this,a=i.components.data,n=a.length,s=[];for(t=0;t<n;++t)o=(e=a[t]).config.setValue,isNaN(o.x)||isNaN(o.y)||(o.index=t,s.push({x:o.x,y:o.y,index:t,data:e,r:1}));i.addJob("kdtree",(function(){i.dataTree=(new r["default"]).buildKdTree(s)}),l.priorityList.kdTree)},o._getHoveredPlot=function(e,t){var o,i,a,n=this.getFromEnv("xAxis"),r=this.getFromEnv("yAxis");if(i=n.getValue(e+n.getTranslation()),a=r.getValue(t+r.getTranslation()),o=this.dataTree&&this.dataTree.getNeighbour({x:i,y:a,options:this.zoomRadiusOb},!0))return o.data.x=o.x,o.data.y=o.y,{pointIndex:o.index||o.i,hovered:!0,pointObj:o.data}},o._decideTooltipType=function(e,t){var o=this.getFromEnv("toolTipController"),i=this.config.currentToolTip,a=this.components.data[e],n=a&&(a.config.finalTooltext||a.config.toolText),r=t.originalEvent;n&&(i?o.draw(r,n,i):i=this.config.currentToolTip=o.draw(r,n))},o._firePlotEvent=function(e,t,o){var i,a=this.getFromEnv("chart"),n=this.components,r=this.getFromEnv("toolTipController"),l=n.data[t],s=this.getFromEnv("paper").canvas.style;if(l)switch(i=l.config.setLink,e){case"fc-mouseover":this._decideTooltipType(t,o),this.highlightPoint(this.config.showHoverEffect,l),a.plotEventHandler(this.getGraphicalElement("tracker"),o,"dataplotRollover"),i&&(s.cursor="pointer");break;case"fc-mouseout":r.hide(this.config.currentToolTip),i&&(s.cursor=u),this.highlightPoint(!1),a.plotEventHandler(this.getGraphicalElement("tracker"),o,"dataplotRollout");break;case"fc-click":a.plotEventHandler(this.getGraphicalElement("tracker"),o,"dataplotClick");break;case"fc-mousemove":this._decideTooltipType(t,o)}},o.highlightPoint=function(e,t){var o,i=this.getFromEnv("chart").config,a=this.getFromEnv("animationManager"),n=this.getGraphicalElement("tracker"),r=this.getFromEnv("xAxis"),l=this.getFromEnv("yAxis"),s=this&&this.config,h=s&&s.zoomedRadius||0,c=s&&s.hoverCosmetics,d=c&&c.fill,u=t&&t.config.hoverEffects,m=t&&t.config.anchorProps,f=c&&c.borderColor,p=c&&c.borderThickness,x={},v=t&&t.link;e&&(x={r:h,fill:d,stroke:f,"stroke-width":p,cx:r.getPixel(t.x),cy:l.getPixel(t.y)}),a.setAnimationState(e?"mouseover":"mouseout"),o=a.setAnimation({el:n||"circle",attr:e&&x,container:this.getContainer("plotGroup"),component:this,doNotRemove:!0,callback:!e&&g}),e&&o.show(),n||this.addGraphicalElement("tracker",o),t&&o.data("eventArgs",{x:t.x,y:t.y,tooltip:t.config.toolText,link:v,showValue:t.config.showValue,hoverColor:!0===t.config.hoverEffects.enabled?c.plotFillHoverColor:void 0,hoverAlpha:!0===t.config.hoverEffects.enabled?c.plotFillHoverAlpha:void 0,anchorBgColor:m.bgColor,anchorBgAlpha:m.anchorBgAlpha,anchorAlpha:m.anchorAlpha,anchorBorderColor:m.borderColor,anchorBorderThickness:!0===t.config.hoverEffects.enabled?p:m.borderThickness,anchorRadius:m.radius,anchorSides:m.sides,anchorStartAngle:m.startAngle,anchorHoverSides:u.anchorSides}),i.lastHoveredPoint=t,x.cursor=v?"pointer":""},o.drawCommonElements=function(){},o.animateCommonElements=function(){},o.remove=function(){e.prototype.remove.call(this),this._deleteGridImages()},o.drawPlots=function(){var e,t,o,i,a,n=this.getFromEnv("animationManager"),r=this.getFromEnv("xAxis"),l=this.getFromEnv("yAxis"),s=this.config,h=this.getContainer("plotGroup"),c=this.getContainer("containerLine"),g=this.getContainer("containerPlot"),d=!1,u=this.config.anchorBorderThickness;this.saveScrollPos(),i=n.setAnimation({el:c||"group",attr:{name:"lineGroup"},container:h,component:this,label:"group"}),a=n.setAnimation({el:g||"group",attr:{name:"plotGroup"},container:h,component:this,label:"group"}),this.getState("visible")?(i.show(),a.show()):(i.hide(),a.hide()),!c&&this.addContainer("containerLine",i),!g&&this.addContainer("containerPlot",a),(this.hasDrawingRefChanged()||this.wasLastDrawPixelated||this.getState("dirty"))&&(this.wasLastDrawPixelated=!1,this.calculateZoomedRadius(),e=v(r),t=v(l),o=s.radius*Math.min(e,t),this.zoomRadiusOb={rx:r.getValue(o+u)-r.getValue(0),ry:l.getValue(0)-l.getValue(o+u)},this._deleteGridImages(),this._graphics._grid={},d=!0),this._gridDraw(d),this.setState("dirty",!1)},o._deleteGridImages=function(){var e,t,o,i,a,n,r,l,s=this.config,h=this._graphics,c=h._imagePool||(h._imagePool=[]),g=h._canvasPool||(h._canvasPool=[]),d=h._lineImagePool||(h._lineImagePool=[]),u=h._lineCanvasPool||(h._lineCanvasPool=[]),m=h._grid||[],f=s._batchDrawTimers;if(f&&f.length)for(;f.length;)this.removeJob(f.shift());for(n in m)if(l=m[n])for(r in l)(a=l[r])&&a.drawState&&((e=a.image).attr({src:"",width:0,height:0}),c.push(e),delete a.image,i=a.canvas,g.push(i),delete a.canvas,delete a.ctx,(t=a.lineImage)&&(t.attr({src:"",width:0,height:0}),d.push(t),delete a.lineImage,o=a.lineCanvas,u.push(o),delete a.lineCanvas,delete a.lineCtx));delete h._grid},o._gridDraw=function(e){var t=this,o=t.config;clearTimeout(o.timer),e?t._gridManager():o.timer=t.addJob("_gridManagerId",(function(){t._gridManager()}),l.priorityList.label)},o.getAllGrids=function(){var e,t,o,i,a,n,r,l,s,h=this.config,c=this.getFromEnv("chart").config,g=this.getFromEnv("xAxis"),d=this.getFromEnv("yAxis"),u=T(g),m=T(d),f=y(u.focusedGrid),p=y(m.focusedGrid),x=[],b=[],P=0,S=0,k=this._graphics._grid,C={},w=v(g),E=v(d),M=Math.ceil(E),F=Math.ceil(w),_=Math.max(m.focusedGrid-1,0),I=Math.min(m.focusedGrid+1,M-1),A=Math.max(u.focusedGrid-1,0),N=Math.min(u.focusedGrid+1,F-1),D=g.getAxisConfig("axisDimention").axisLength||c.canvasWidth,R=d.getAxisConfig("axisDimention").axisLength||c.canvasHeight,V=g.config,L=d.config,G=V.axisRange.min,W=V.axisRange.max,B=L.axisRange.min,Z=L.axisRange.max,H=h.radius*Math.min(w,E)+h.plotCosmetics.borderWidth,z=Math.abs(H/(D*w/(g.config.axisRange.max-g.config.axisRange.min))),O=Math.abs(H/(R*E/(d.config.axisRange.max-d.config.axisRange.min))),J=d.getPixel(d.config.axisRange.max),U=g.getPixel(g.config.axisRange.min);for(k||(this.config.grids=k={}),P=_;P<=I;++P)for(k[P]=C=k[P]||{},l=J+P*R,i=d.getValue(l),a=d.getValue(l+R),S=A;S<=N;++S)r=S===F-1?H:0,s=U+S*D-(n=0===S?H:0),o=g.getValue(s),t=g.getValue(s+D+n+r),C[S]=e=C[S]||{xPixel:s,width:Math.abs(g.getPixel(t)-s),yPixel:l,height:d.getPixel(a)-l,xLeftValue:o,yTopValue:i,xRightValue:t,yBottomValue:a,drawState:0,xMinWPad:Math.max(Math.min(o,t)-z,G),yMinWPad:Math.max(Math.min(i,a)-O,B),xMaxWPad:Math.min(Math.max(o,t)+z,W),yMaxWPad:Math.min(Math.max(i,a)+O,Z),i:P,j:S},e.drawState||(~u.indexOf(S)&&~m.indexOf(P)?x.push(e):~f.indexOf(S)&&~p.indexOf(P)&&b.push(e));return{focused:x,nearBy:b}},o.allocatePosition=function(){var e,t,o,i,a,n=this.config,r=this.components.data,l=r.length,s=this.getFromEnv("xAxis"),h=this.getFromEnv("yAxis"),c=n.zoomedRadius;for(o=0;o<l;o++)i=(a=r[o].config).setValue,e=s.getPixel(i.x),t=h.getPixel(i.y),a.props={element:{attr:{polypath:[0,e,t,c||a.anchorProps.radius]}},label:{attr:{}}}},o._gridManager=function(){var e,t,o=this;e=o.getAllGrids(),o.config._drawGrid=e.focused,(e.focused.length||e.nearBy.length)&&(o.config._drawGrid=e.focused,t=function(){o.config._drawGrid=e.nearBy,o._drawGridArr()},o._drawGridArr(t))},o._drawGridArr=function(e){var t,o,i,a,n,r,l,s,h,g=this.config,d=g.drawLine,u=g._drawGrid,m=[],f=this.getFromEnv("animationManager"),p=this.getContainer("containerLine"),x=this.getContainer("containerPlot"),y=this.getFromEnv("xAxis"),b=this.getFromEnv("yAxis"),P=v(y),S=v(b),T=this._graphics._imagePool||[],k=this._graphics._canvasPool||[],C=this._graphics._lineImagePool||[],w=this._graphics._lineCanvasPool||[],E=g.plotCosmetics,M=g.radius*Math.min(P,S);if(u.length){for(;u.length;)s=(t=u.shift()).xPixel,h=t.yPixel,r=t.width,l=t.height,2!==t.drawState&&(t.drawState=2,d&&(C.length&&(t.lineImage=C.shift()),t.lineImage=f.setAnimation({el:t.lineImage||"image",attr:{x:s,y:h,width:r,height:l},container:p,component:this,label:"image"}),w.length?t.lineCanvas=i=w.shift():t.lineCanvas=i=c.document.createElement("canvas"),i.setAttribute("width",r),i.setAttribute("height",l),(n=t.lineCtx=i.getContext("2d")).fillStyle=E.fillStyle,n.strokeStyle=E.lineStrokeStyle,n.lineWidth=E.lineWidth),T.length&&(t.image=T.shift()),t.image=f.setAnimation({el:t.image||"image",attr:{x:s,y:h,width:r,height:l},container:x,component:this,label:"image"}),k.length?t.canvas=o=k.shift():t.canvas=o=c.document.createElement("canvas"),o.setAttribute("width",r),o.setAttribute("height",l),a=t.ctx=o.getContext("2d"),M<1?(a.strokeStyle=E.fillStyle,a.lineWidth=.5):(a.fillStyle=E.fillStyle,a.strokeStyle=E.strokeStyle,a.lineWidth=E.borderWidth),m.push(t));g._batchDrawindex=this.config.JSONData.data&&this.config.JSONData.data.length-1||0,this._drawGridArrBatch(m,e,!g.animation.enabled)}else e&&e()},o._drawGridArrBatch=function(e,t,o){var i,a,n,r,h,c,g,d,u,f,p,v,y,b,S,T,k,C,w,E,M,F,_,I,A=this,N=A.config,D=N.drawLine,R=N.plotCosmetics,V=N._batchDrawindex,L=A.components.data,G=V-N.chunkSize,W=A.getFromEnv("xAxis"),B=A.getFromEnv("yAxis"),Z=A.getFromEnv("chart"),H=A.getFromEnv("animationManager"),z=Z.getFromEnv("dataSource"),O=N.JSONData,J=N.zoomedRadius,U=(0,s.pluckNumber)(O.showregressionline,Z.config.showregressionline,0),j=N._store||[],K=R.lineWidth||J<1;for(U&&(E=(0,s.toRaphaelColor)((0,s.pluck)(O.regressionlinecolor,z.chart.regressionlinecolor,N.anchorbordercolor,N.lineColor,"fff000")),M=(0,s.pluckNumber)(O.regressionlinethickness,z.chart.regressionlinethickness,1),F=(0,s.pluckNumber)(O.regressionlinealpha,z.chart.regressionlinealpha,100)/100),d=0;d<e.length;d+=1)e[d].ctx.beginPath(),D&&e[d].lineCtx.beginPath();for(G=G<=0?0:G;V>=G;V-=1)if((v=L[V]&&L[V].config.setValue)&&!isNaN(v.x)&&!isNaN(v.y))for(d=0;d<e.length;d+=1)u=e[d],P(v.x,u.xMinWPad,u.xMaxWPad)&&P(v.y,u.yMinWPad,u.yMaxWPad)?(f=u.ctx,p=u.lineCtx,i=W.getPixel(v.x)-u.xPixel,a=B.getPixel(v.y)-u.yPixel,(g=j[i])||(g=j[i]={}),g[a]||(g[a]=!0,D&&(y=V&&L[V-1].config.setValue,b=V<L.length-1&&L[V+1].config.setValue,!y||isNaN(y.x)||isNaN(y.y)||(_=W.getPixel(y.x)-u.xPixel,I=B.getPixel(y.y)-u.yPixel,p.moveTo(Math.round(_),Math.round(I)),p.lineTo(i,a),P(b.x,u.xMinWPad,u.xMaxWPad)&&P(b.y,u.yMinWPad,u.yMaxWPad)||isNaN(b.x)||isNaN(b.y)||p.lineTo(W.getPixel(b.x)-u.xPixel,B.getPixel(b.y)-u.yPixel))),J<1?(f.moveTo(i,a),f.lineTo(i+1,a)):(f.moveTo(i+J,a),f.arc(i,a,J,0,m)))):D&&V&&x(v,L[V-1].config.setValue,u)&&(y=L[V-1].config.setValue,_=W.getPixel(y.x)-u.xPixel,I=B.getPixel(y.y)-u.yPixel,i=W.getPixel(v.x)-u.xPixel,a=B.getPixel(v.y)-u.yPixel,(p=u.lineCtx).moveTo(Math.round(_),Math.round(I)),p.lineTo(i,a));for(d=0;d<e.length;d+=1)(f=(u=e[d]).ctx).fill(),K&&f.stroke(),f.closePath(),D&&(p=u.lineCtx,K&&p.stroke(),p.closePath());if(N._batchDrawindex=V,V>=0){if(!o)for(d=0;d<e.length;d+=1)k=e[d].image,C=e[d].canvas,H.setAnimation({el:k,attr:{src:C.toDataURL("image/png")},component:A}),N.drawLine&&(S=e[d].lineImage,T=e[d].lineCanvas,H.setAnimation({el:S,src:C.toDataURL("image/png"),component:A}));(N._batchDrawTimers||(N._batchDrawTimers=[])).push(A.addJob("_drawGridArrBatchID",(function(){A.getFromEnv("chart")&&A._drawGridArrBatch(e,t,o)}),l.priorityList.draw))}else{if(A.setupKdTree(),delete N._store,U)for(w=N.regressionPoints,d=0;d<e.length;d+=1)k=(u=e[d]).image,C=u.canvas,f=u.ctx,w.length&&(n=W.getPixel(w[0].x)-u.xPixel,h=B.getPixel(w[0].y)-u.yPixel,r=W.getPixel(w[1].x)-u.xPixel,c=B.getPixel(w[1].y)-u.yPixel,f.beginPath(),f.strokeStyle=E,f.lineWidth=M,f.globalAlpha=F,f.moveTo(n,h),f.lineTo(r,c),f.stroke(),f.closePath());for(d=0;d<e.length;d+=1)k=(u=e[d]).image,C=u.canvas,u.drawState=1,H.setAnimation({el:k,attr:{src:C.toDataURL("image/png")},component:A}),D&&(S=u.lineImage,T=u.lineCanvas,H.setAnimation({el:S,attr:{src:T.toDataURL("image/png")},component:A}));t&&t()}},o.getRegressionPoints=function(){var e,t,o=this.config.regressionPoints,i=-Infinity,a=Infinity,n=-Infinity,r=Infinity;if(o&&o.length){for(t=o.length,e=0;e<t;e++)i=Math.max(i,o[e].x),a=Math.min(a,o[e].x),n=Math.max(n,o[e].y),r=Math.min(r,o[e].y);return{max:n,min:r,xMax:i,xMin:a}}},o.show=function(){var e=this.getContainer("containerLine"),t=this.getContainer("containerPlot"),o=this.getFromEnv("legend");o&&o.getItem(this.config.legendItemId)&&o.getItem(this.config.legendItemId).removeLegendState("hidden"),this.setState("visible",!0),e.show(),t.show(),this.setState("dirty",!0)},o.setContainerVisibility=function(){},o.draw=function(){var e=this,t=e.config,o=e.getFromEnv("xAxis"),i=o.getPixel(0),a=o.getPixel(1),n=e.getFromEnv("groupMaxWidth"),r=t.drawn,s=(e.getSkippingInfo&&e.getSkippingInfo()||{}).skippingApplied;n||(n=Math.abs(a-i),e.addToEnv("groupMaxWidth",n)),!r&&e.createContainer(),e.setContainerVisibility(!0),s&&e.hidePlots(),e.drawPlots(),e.drawCommonElements&&!e.config.skipCommonElements&&e.drawCommonElements(),t.drawn?e.drawLabel(void 0,void 0):e.addJob("labelDrawID",(function(){e.drawLabel(void 0,void 0)}),l.priorityList.label),t.drawn=!0,e.removePlots()},o.hide=function(){var e=this.getContainer("containerLine"),t=this.getContainer("containerPlot"),o=this.getFromEnv("legend");o&&o.getItem(this.config.legendItemId)&&o.getItem(this.config.legendItemId).setLegendState("hidden"),e.hide(),t.hide(),this.setState("dirty",!0),this.setState("visible",!1)},o._addLegend=function(){var e,t,o=this.getFromEnv("chart"),i=o.getFromEnv("dataSource").chart,a=o.getFromEnv("legend"),n=this.config,r=this.config.JSONData,l=(0,s.pluck)(r.anchorbordercolor,i.anchorbordercolor),h=(0,s.getFirstColor)((0,s.pluck)(l,n.plotBorderColor)),c=(0,s.getFirstColor)((0,s.pluck)(r.anchorbgcolor,r.color,i.anchorbgcolor,n.plotColor)),g=(0,s.pluck)(r.anchoralpha,r.alpha,i.anchoralpha,s.HUNDREDSTRING),d=(0,s.pluck)(r.anchorbgalpha,r.alpha,i.anchorbgalpha,s.HUNDREDSTRING),u=S(c,g*d/100),m=S(h,g);t={enabled:n.includeInLegend,type:this.type,anchorSide:2,label:(0,s.getFirstValue)(this.config.JSONData.seriesname)},n.includeinlegend?((e=a.getItem(this.config.legendItemId))?e.configure({style:a.config.itemStyle,hiddenStyle:a.config.itemHiddenStyle,datasetVisible:a.config.datasetVisible,hoverStyle:a.config.itemHoverStyle}):(this.config.legendItemId=a.createItem(this),e=a.getItem(this.config.legendItemId),this.addExtEventListener("fc-click",(function(){e.itemClickFn()}),e)),e.configure(t),e.setStateCosmetics("default",{symbol:{fill:u,stroke:m,rawFillColor:c,rawStrokeColor:n.anchorbordercolor,"stroke-width":n.anchorBorderThickness}}),this.getState("visible")?e.removeLegendState("hidden"):e.setLegendState("hidden")):this.config.legendItemId&&a.disposeItem(this.config.legendItemId)},o._setConfigure=function(){var e,t,o,i,a,n,r,l,c,g,d,u,m=-Infinity,f=+Infinity,p=m,x=f,v=f,y=m,b=this.components.data||(this.components.data=[]),P=this.getFromEnv("chart"),S=this.config,T=this.config.JSONData,k=P.getFromEnv("dataSource").chart,C=T.data||[],w=C.length,E=this.getFromEnv("number-formatter"),M=(0,s.parseUnsafeString)(k.yaxisname),F=(0,s.parseUnsafeString)(k.xaxisname),_=S.lineDashed,I=S.lineDashStyle,A=(0,s.pluckNumber)(T.showregressionline,P.config.showregressionline,0),N=(0,s.pluckNumber)(T.showyonx,k.showyonx,1),D=S.parentYAxis,R=S.toolTipSepChar,V=S.seriesname;for(e=0;e<w;e+=1)i=C[e],(t=(o=b[e]||(b[e]={})).config||(o.config={})).setValue=a={x:E.getCleanValue(i.x),y:E.getCleanValue(i.y),index:e},y<a.x&&(y=a.x,S.rightMostData=o),v>a.x&&(v=a.x,S.leftMostData=o),p=Math.max(p,a.y),x=Math.min(x,a.y),S.showRegressionLine&&this.pointValueWatcher(a.x,a.y,S.regressionObj),t.setLink=(0,s.pluck)(i.link),t.anchorProps=this._parseAnchorProperties(e),t.showValue=(0,s.pluckNumber)(i.showvalue,S.showValues),t.dashed=(0,s.pluckNumber)(i.dashed,_),t.color=(0,s.pluck)(i.color,S.lineColor),t.alpha=(0,s.pluck)(i.alpha,S.lineAlpha),t.dashStyle=t.dashed?I:"none",t.toolTipValue=r=E.dataLabels(a.y,D),t.setDisplayValue=u=(0,s.parseUnsafeString)(i.displayvalue),g=t.formatedVal=(0,s.pluck)(i.toolTipValue,E.dataLabels(a.y,D)),d=E.xAxis(a.x),t.displayValue=(0,s.pluck)(u,r),t.setTooltext=(0,s.getValidValue)((0,s.parseUnsafeString)((0,s.pluck)(i.tooltext,S.plotToolText))),S.showTooltip?void 0!==t.setTooltext?(l=[4,5,6,7,8,9,10,11],c={yaxisName:M,xaxisName:F,yDataValue:g,xDataValue:d},n=(0,s.parseTooltext)(t.setTooltext,l,c,i,k,T)):null===g?n=!1:(n=V?V+R:s.BLANKSTRING,n+=a.x?d+R:s.BLANKSTRING,n+=r):n=!1,t.toolText=n,o?o.graphics||(b[e].graphics={}):o=b[e]={graphics:{}},t.hoverEffects=this._parseHoverEffectOptions(o),t.anchorProps.isAnchorHoverRadius=t.hoverEffects.anchorRadius;S.xMax=y,S.xMin=v,S.yMin=x,S.yMax=p,S.regressionPoints=A?(0,h["default"])(T.data.slice(),N)[1]:[],this.ErrorValueConfigure&&this.ErrorValueConfigure()},t}(n["default"]);t["default"]=k}}])}));
//# sourceMappingURL=http://localhost:3052/3.15.0-sr.1/map/licensed/fusioncharts.zoomscatter.js.map