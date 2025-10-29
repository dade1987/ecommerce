(function(global2, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() : typeof define === "function" && define.amd ? define(factory) : (global2 = typeof globalThis !== "undefined" ? globalThis : global2 || self, global2.EnjoyHen = factory());
})(this, function() {
  "use strict";
  var _documentCurrentScript = typeof document !== "undefined" ? document.currentScript : null;
  /**
  * @vue/shared v3.5.22
  * (c) 2018-present Yuxi (Evan) You and Vue contributors
  * @license MIT
  **/
  // @__NO_SIDE_EFFECTS__
  function makeMap$1(str) {
    const map = /* @__PURE__ */ Object.create(null);
    for (const key of str.split(","))
      map[key] = 1;
    return (val) => val in map;
  }
  const EMPTY_OBJ = {};
  const EMPTY_ARR = [];
  const NOOP = () => {
  };
  const NO = () => false;
  const isOn$1 = (key) => key.charCodeAt(0) === 111 && key.charCodeAt(1) === 110 && // uppercase letter
  (key.charCodeAt(2) > 122 || key.charCodeAt(2) < 97);
  const isModelListener$1 = (key) => key.startsWith("onUpdate:");
  const extend$1 = Object.assign;
  const remove = (arr, el) => {
    const i = arr.indexOf(el);
    if (i > -1) {
      arr.splice(i, 1);
    }
  };
  const hasOwnProperty$2 = Object.prototype.hasOwnProperty;
  const hasOwn$1 = (val, key) => hasOwnProperty$2.call(val, key);
  const isArray$1 = Array.isArray;
  const isMap = (val) => toTypeString$1(val) === "[object Map]";
  const isSet = (val) => toTypeString$1(val) === "[object Set]";
  const isFunction$1 = (val) => typeof val === "function";
  const isString$1 = (val) => typeof val === "string";
  const isSymbol$1 = (val) => typeof val === "symbol";
  const isObject = (val) => val !== null && typeof val === "object";
  const isPromise = (val) => {
    return (isObject(val) || isFunction$1(val)) && isFunction$1(val.then) && isFunction$1(val.catch);
  };
  const objectToString$1 = Object.prototype.toString;
  const toTypeString$1 = (value) => objectToString$1.call(value);
  const toRawType = (value) => {
    return toTypeString$1(value).slice(8, -1);
  };
  const isPlainObject$1 = (val) => toTypeString$1(val) === "[object Object]";
  const isIntegerKey = (key) => isString$1(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
  const isReservedProp = /* @__PURE__ */ makeMap$1(
    // the leading comma is intentional so empty string "" is also included
    ",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"
  );
  const cacheStringFunction$1 = (fn) => {
    const cache = /* @__PURE__ */ Object.create(null);
    return (str) => {
      const hit = cache[str];
      return hit || (cache[str] = fn(str));
    };
  };
  const camelizeRE$1 = /-\w/g;
  const camelize$1 = cacheStringFunction$1(
    (str) => {
      return str.replace(camelizeRE$1, (c) => c.slice(1).toUpperCase());
    }
  );
  const hyphenateRE$1 = /\B([A-Z])/g;
  const hyphenate$1 = cacheStringFunction$1(
    (str) => str.replace(hyphenateRE$1, "-$1").toLowerCase()
  );
  const capitalize$1 = cacheStringFunction$1((str) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
  });
  const toHandlerKey = cacheStringFunction$1(
    (str) => {
      const s = str ? `on${capitalize$1(str)}` : ``;
      return s;
    }
  );
  const hasChanged = (value, oldValue) => !Object.is(value, oldValue);
  const invokeArrayFns = (fns, ...arg) => {
    for (let i = 0; i < fns.length; i++) {
      fns[i](...arg);
    }
  };
  const def = (obj, key, value, writable = false) => {
    Object.defineProperty(obj, key, {
      configurable: true,
      enumerable: false,
      writable,
      value
    });
  };
  const looseToNumber = (val) => {
    const n = parseFloat(val);
    return isNaN(n) ? val : n;
  };
  let _globalThis;
  const getGlobalThis = () => {
    return _globalThis || (_globalThis = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : {});
  };
  function normalizeStyle(value) {
    if (isArray$1(value)) {
      const res = {};
      for (let i = 0; i < value.length; i++) {
        const item = value[i];
        const normalized = isString$1(item) ? parseStringStyle(item) : normalizeStyle(item);
        if (normalized) {
          for (const key in normalized) {
            res[key] = normalized[key];
          }
        }
      }
      return res;
    } else if (isString$1(value) || isObject(value)) {
      return value;
    }
  }
  const listDelimiterRE = /;(?![^(]*\))/g;
  const propertyDelimiterRE = /:([^]+)/;
  const styleCommentRE = /\/\*[^]*?\*\//g;
  function parseStringStyle(cssText2) {
    const ret = {};
    cssText2.replace(styleCommentRE, "").split(listDelimiterRE).forEach((item) => {
      if (item) {
        const tmp = item.split(propertyDelimiterRE);
        tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
      }
    });
    return ret;
  }
  function normalizeClass(value) {
    let res = "";
    if (isString$1(value)) {
      res = value;
    } else if (isArray$1(value)) {
      for (let i = 0; i < value.length; i++) {
        const normalized = normalizeClass(value[i]);
        if (normalized) {
          res += normalized + " ";
        }
      }
    } else if (isObject(value)) {
      for (const name in value) {
        if (value[name]) {
          res += name + " ";
        }
      }
    }
    return res.trim();
  }
  /**
  * @vue/reactivity v3.5.22
  * (c) 2018-present Yuxi (Evan) You and Vue contributors
  * @license MIT
  **/
  let activeEffectScope;
  class EffectScope {
    constructor(detached = false) {
      this.detached = detached;
      this._active = true;
      this._on = 0;
      this.effects = [];
      this.cleanups = [];
      this._isPaused = false;
      this.parent = activeEffectScope;
      if (!detached && activeEffectScope) {
        this.index = (activeEffectScope.scopes || (activeEffectScope.scopes = [])).push(
          this
        ) - 1;
      }
    }
    get active() {
      return this._active;
    }
    pause() {
      if (this._active) {
        this._isPaused = true;
        let i, l;
        if (this.scopes) {
          for (i = 0, l = this.scopes.length; i < l; i++) {
            this.scopes[i].pause();
          }
        }
        for (i = 0, l = this.effects.length; i < l; i++) {
          this.effects[i].pause();
        }
      }
    }
    /**
     * Resumes the effect scope, including all child scopes and effects.
     */
    resume() {
      if (this._active) {
        if (this._isPaused) {
          this._isPaused = false;
          let i, l;
          if (this.scopes) {
            for (i = 0, l = this.scopes.length; i < l; i++) {
              this.scopes[i].resume();
            }
          }
          for (i = 0, l = this.effects.length; i < l; i++) {
            this.effects[i].resume();
          }
        }
      }
    }
    run(fn) {
      if (this._active) {
        const currentEffectScope = activeEffectScope;
        try {
          activeEffectScope = this;
          return fn();
        } finally {
          activeEffectScope = currentEffectScope;
        }
      }
    }
    /**
     * This should only be called on non-detached scopes
     * @internal
     */
    on() {
      if (++this._on === 1) {
        this.prevScope = activeEffectScope;
        activeEffectScope = this;
      }
    }
    /**
     * This should only be called on non-detached scopes
     * @internal
     */
    off() {
      if (this._on > 0 && --this._on === 0) {
        activeEffectScope = this.prevScope;
        this.prevScope = void 0;
      }
    }
    stop(fromParent) {
      if (this._active) {
        this._active = false;
        let i, l;
        for (i = 0, l = this.effects.length; i < l; i++) {
          this.effects[i].stop();
        }
        this.effects.length = 0;
        for (i = 0, l = this.cleanups.length; i < l; i++) {
          this.cleanups[i]();
        }
        this.cleanups.length = 0;
        if (this.scopes) {
          for (i = 0, l = this.scopes.length; i < l; i++) {
            this.scopes[i].stop(true);
          }
          this.scopes.length = 0;
        }
        if (!this.detached && this.parent && !fromParent) {
          const last = this.parent.scopes.pop();
          if (last && last !== this) {
            this.parent.scopes[this.index] = last;
            last.index = this.index;
          }
        }
        this.parent = void 0;
      }
    }
  }
  function getCurrentScope() {
    return activeEffectScope;
  }
  let activeSub;
  const pausedQueueEffects = /* @__PURE__ */ new WeakSet();
  class ReactiveEffect {
    constructor(fn) {
      this.fn = fn;
      this.deps = void 0;
      this.depsTail = void 0;
      this.flags = 1 | 4;
      this.next = void 0;
      this.cleanup = void 0;
      this.scheduler = void 0;
      if (activeEffectScope && activeEffectScope.active) {
        activeEffectScope.effects.push(this);
      }
    }
    pause() {
      this.flags |= 64;
    }
    resume() {
      if (this.flags & 64) {
        this.flags &= -65;
        if (pausedQueueEffects.has(this)) {
          pausedQueueEffects.delete(this);
          this.trigger();
        }
      }
    }
    /**
     * @internal
     */
    notify() {
      if (this.flags & 2 && !(this.flags & 32)) {
        return;
      }
      if (!(this.flags & 8)) {
        batch(this);
      }
    }
    run() {
      if (!(this.flags & 1)) {
        return this.fn();
      }
      this.flags |= 2;
      cleanupEffect(this);
      prepareDeps(this);
      const prevEffect = activeSub;
      const prevShouldTrack = shouldTrack;
      activeSub = this;
      shouldTrack = true;
      try {
        return this.fn();
      } finally {
        cleanupDeps(this);
        activeSub = prevEffect;
        shouldTrack = prevShouldTrack;
        this.flags &= -3;
      }
    }
    stop() {
      if (this.flags & 1) {
        for (let link = this.deps; link; link = link.nextDep) {
          removeSub(link);
        }
        this.deps = this.depsTail = void 0;
        cleanupEffect(this);
        this.onStop && this.onStop();
        this.flags &= -2;
      }
    }
    trigger() {
      if (this.flags & 64) {
        pausedQueueEffects.add(this);
      } else if (this.scheduler) {
        this.scheduler();
      } else {
        this.runIfDirty();
      }
    }
    /**
     * @internal
     */
    runIfDirty() {
      if (isDirty(this)) {
        this.run();
      }
    }
    get dirty() {
      return isDirty(this);
    }
  }
  let batchDepth = 0;
  let batchedSub;
  let batchedComputed;
  function batch(sub, isComputed = false) {
    sub.flags |= 8;
    if (isComputed) {
      sub.next = batchedComputed;
      batchedComputed = sub;
      return;
    }
    sub.next = batchedSub;
    batchedSub = sub;
  }
  function startBatch() {
    batchDepth++;
  }
  function endBatch() {
    if (--batchDepth > 0) {
      return;
    }
    if (batchedComputed) {
      let e = batchedComputed;
      batchedComputed = void 0;
      while (e) {
        const next = e.next;
        e.next = void 0;
        e.flags &= -9;
        e = next;
      }
    }
    let error;
    while (batchedSub) {
      let e = batchedSub;
      batchedSub = void 0;
      while (e) {
        const next = e.next;
        e.next = void 0;
        e.flags &= -9;
        if (e.flags & 1) {
          try {
            ;
            e.trigger();
          } catch (err) {
            if (!error)
              error = err;
          }
        }
        e = next;
      }
    }
    if (error)
      throw error;
  }
  function prepareDeps(sub) {
    for (let link = sub.deps; link; link = link.nextDep) {
      link.version = -1;
      link.prevActiveLink = link.dep.activeLink;
      link.dep.activeLink = link;
    }
  }
  function cleanupDeps(sub) {
    let head;
    let tail = sub.depsTail;
    let link = tail;
    while (link) {
      const prev = link.prevDep;
      if (link.version === -1) {
        if (link === tail)
          tail = prev;
        removeSub(link);
        removeDep(link);
      } else {
        head = link;
      }
      link.dep.activeLink = link.prevActiveLink;
      link.prevActiveLink = void 0;
      link = prev;
    }
    sub.deps = head;
    sub.depsTail = tail;
  }
  function isDirty(sub) {
    for (let link = sub.deps; link; link = link.nextDep) {
      if (link.dep.version !== link.version || link.dep.computed && (refreshComputed(link.dep.computed) || link.dep.version !== link.version)) {
        return true;
      }
    }
    if (sub._dirty) {
      return true;
    }
    return false;
  }
  function refreshComputed(computed2) {
    if (computed2.flags & 4 && !(computed2.flags & 16)) {
      return;
    }
    computed2.flags &= -17;
    if (computed2.globalVersion === globalVersion) {
      return;
    }
    computed2.globalVersion = globalVersion;
    if (!computed2.isSSR && computed2.flags & 128 && (!computed2.deps && !computed2._dirty || !isDirty(computed2))) {
      return;
    }
    computed2.flags |= 2;
    const dep = computed2.dep;
    const prevSub = activeSub;
    const prevShouldTrack = shouldTrack;
    activeSub = computed2;
    shouldTrack = true;
    try {
      prepareDeps(computed2);
      const value = computed2.fn(computed2._value);
      if (dep.version === 0 || hasChanged(value, computed2._value)) {
        computed2.flags |= 128;
        computed2._value = value;
        dep.version++;
      }
    } catch (err) {
      dep.version++;
      throw err;
    } finally {
      activeSub = prevSub;
      shouldTrack = prevShouldTrack;
      cleanupDeps(computed2);
      computed2.flags &= -3;
    }
  }
  function removeSub(link, soft = false) {
    const { dep, prevSub, nextSub } = link;
    if (prevSub) {
      prevSub.nextSub = nextSub;
      link.prevSub = void 0;
    }
    if (nextSub) {
      nextSub.prevSub = prevSub;
      link.nextSub = void 0;
    }
    if (dep.subs === link) {
      dep.subs = prevSub;
      if (!prevSub && dep.computed) {
        dep.computed.flags &= -5;
        for (let l = dep.computed.deps; l; l = l.nextDep) {
          removeSub(l, true);
        }
      }
    }
    if (!soft && !--dep.sc && dep.map) {
      dep.map.delete(dep.key);
    }
  }
  function removeDep(link) {
    const { prevDep, nextDep } = link;
    if (prevDep) {
      prevDep.nextDep = nextDep;
      link.prevDep = void 0;
    }
    if (nextDep) {
      nextDep.prevDep = prevDep;
      link.nextDep = void 0;
    }
  }
  let shouldTrack = true;
  const trackStack = [];
  function pauseTracking() {
    trackStack.push(shouldTrack);
    shouldTrack = false;
  }
  function resetTracking() {
    const last = trackStack.pop();
    shouldTrack = last === void 0 ? true : last;
  }
  function cleanupEffect(e) {
    const { cleanup } = e;
    e.cleanup = void 0;
    if (cleanup) {
      const prevSub = activeSub;
      activeSub = void 0;
      try {
        cleanup();
      } finally {
        activeSub = prevSub;
      }
    }
  }
  let globalVersion = 0;
  class Link {
    constructor(sub, dep) {
      this.sub = sub;
      this.dep = dep;
      this.version = dep.version;
      this.nextDep = this.prevDep = this.nextSub = this.prevSub = this.prevActiveLink = void 0;
    }
  }
  class Dep {
    // TODO isolatedDeclarations "__v_skip"
    constructor(computed2) {
      this.computed = computed2;
      this.version = 0;
      this.activeLink = void 0;
      this.subs = void 0;
      this.map = void 0;
      this.key = void 0;
      this.sc = 0;
      this.__v_skip = true;
    }
    track(debugInfo) {
      if (!activeSub || !shouldTrack || activeSub === this.computed) {
        return;
      }
      let link = this.activeLink;
      if (link === void 0 || link.sub !== activeSub) {
        link = this.activeLink = new Link(activeSub, this);
        if (!activeSub.deps) {
          activeSub.deps = activeSub.depsTail = link;
        } else {
          link.prevDep = activeSub.depsTail;
          activeSub.depsTail.nextDep = link;
          activeSub.depsTail = link;
        }
        addSub(link);
      } else if (link.version === -1) {
        link.version = this.version;
        if (link.nextDep) {
          const next = link.nextDep;
          next.prevDep = link.prevDep;
          if (link.prevDep) {
            link.prevDep.nextDep = next;
          }
          link.prevDep = activeSub.depsTail;
          link.nextDep = void 0;
          activeSub.depsTail.nextDep = link;
          activeSub.depsTail = link;
          if (activeSub.deps === link) {
            activeSub.deps = next;
          }
        }
      }
      return link;
    }
    trigger(debugInfo) {
      this.version++;
      globalVersion++;
      this.notify(debugInfo);
    }
    notify(debugInfo) {
      startBatch();
      try {
        if (false)
          ;
        for (let link = this.subs; link; link = link.prevSub) {
          if (link.sub.notify()) {
            ;
            link.sub.dep.notify();
          }
        }
      } finally {
        endBatch();
      }
    }
  }
  function addSub(link) {
    link.dep.sc++;
    if (link.sub.flags & 4) {
      const computed2 = link.dep.computed;
      if (computed2 && !link.dep.subs) {
        computed2.flags |= 4 | 16;
        for (let l = computed2.deps; l; l = l.nextDep) {
          addSub(l);
        }
      }
      const currentTail = link.dep.subs;
      if (currentTail !== link) {
        link.prevSub = currentTail;
        if (currentTail)
          currentTail.nextSub = link;
      }
      link.dep.subs = link;
    }
  }
  const targetMap = /* @__PURE__ */ new WeakMap();
  const ITERATE_KEY = Symbol(
    ""
  );
  const MAP_KEY_ITERATE_KEY = Symbol(
    ""
  );
  const ARRAY_ITERATE_KEY = Symbol(
    ""
  );
  function track(target, type, key) {
    if (shouldTrack && activeSub) {
      let depsMap = targetMap.get(target);
      if (!depsMap) {
        targetMap.set(target, depsMap = /* @__PURE__ */ new Map());
      }
      let dep = depsMap.get(key);
      if (!dep) {
        depsMap.set(key, dep = new Dep());
        dep.map = depsMap;
        dep.key = key;
      }
      {
        dep.track();
      }
    }
  }
  function trigger(target, type, key, newValue, oldValue, oldTarget) {
    const depsMap = targetMap.get(target);
    if (!depsMap) {
      globalVersion++;
      return;
    }
    const run = (dep) => {
      if (dep) {
        {
          dep.trigger();
        }
      }
    };
    startBatch();
    if (type === "clear") {
      depsMap.forEach(run);
    } else {
      const targetIsArray = isArray$1(target);
      const isArrayIndex = targetIsArray && isIntegerKey(key);
      if (targetIsArray && key === "length") {
        const newLength = Number(newValue);
        depsMap.forEach((dep, key2) => {
          if (key2 === "length" || key2 === ARRAY_ITERATE_KEY || !isSymbol$1(key2) && key2 >= newLength) {
            run(dep);
          }
        });
      } else {
        if (key !== void 0 || depsMap.has(void 0)) {
          run(depsMap.get(key));
        }
        if (isArrayIndex) {
          run(depsMap.get(ARRAY_ITERATE_KEY));
        }
        switch (type) {
          case "add":
            if (!targetIsArray) {
              run(depsMap.get(ITERATE_KEY));
              if (isMap(target)) {
                run(depsMap.get(MAP_KEY_ITERATE_KEY));
              }
            } else if (isArrayIndex) {
              run(depsMap.get("length"));
            }
            break;
          case "delete":
            if (!targetIsArray) {
              run(depsMap.get(ITERATE_KEY));
              if (isMap(target)) {
                run(depsMap.get(MAP_KEY_ITERATE_KEY));
              }
            }
            break;
          case "set":
            if (isMap(target)) {
              run(depsMap.get(ITERATE_KEY));
            }
            break;
        }
      }
    }
    endBatch();
  }
  function reactiveReadArray(array) {
    const raw = toRaw(array);
    if (raw === array)
      return raw;
    track(raw, "iterate", ARRAY_ITERATE_KEY);
    return isShallow(array) ? raw : raw.map(toReactive);
  }
  function shallowReadArray(arr) {
    track(arr = toRaw(arr), "iterate", ARRAY_ITERATE_KEY);
    return arr;
  }
  const arrayInstrumentations = {
    __proto__: null,
    [Symbol.iterator]() {
      return iterator(this, Symbol.iterator, toReactive);
    },
    concat(...args) {
      return reactiveReadArray(this).concat(
        ...args.map((x) => isArray$1(x) ? reactiveReadArray(x) : x)
      );
    },
    entries() {
      return iterator(this, "entries", (value) => {
        value[1] = toReactive(value[1]);
        return value;
      });
    },
    every(fn, thisArg) {
      return apply(this, "every", fn, thisArg, void 0, arguments);
    },
    filter(fn, thisArg) {
      return apply(this, "filter", fn, thisArg, (v) => v.map(toReactive), arguments);
    },
    find(fn, thisArg) {
      return apply(this, "find", fn, thisArg, toReactive, arguments);
    },
    findIndex(fn, thisArg) {
      return apply(this, "findIndex", fn, thisArg, void 0, arguments);
    },
    findLast(fn, thisArg) {
      return apply(this, "findLast", fn, thisArg, toReactive, arguments);
    },
    findLastIndex(fn, thisArg) {
      return apply(this, "findLastIndex", fn, thisArg, void 0, arguments);
    },
    // flat, flatMap could benefit from ARRAY_ITERATE but are not straight-forward to implement
    forEach(fn, thisArg) {
      return apply(this, "forEach", fn, thisArg, void 0, arguments);
    },
    includes(...args) {
      return searchProxy(this, "includes", args);
    },
    indexOf(...args) {
      return searchProxy(this, "indexOf", args);
    },
    join(separator) {
      return reactiveReadArray(this).join(separator);
    },
    // keys() iterator only reads `length`, no optimization required
    lastIndexOf(...args) {
      return searchProxy(this, "lastIndexOf", args);
    },
    map(fn, thisArg) {
      return apply(this, "map", fn, thisArg, void 0, arguments);
    },
    pop() {
      return noTracking(this, "pop");
    },
    push(...args) {
      return noTracking(this, "push", args);
    },
    reduce(fn, ...args) {
      return reduce(this, "reduce", fn, args);
    },
    reduceRight(fn, ...args) {
      return reduce(this, "reduceRight", fn, args);
    },
    shift() {
      return noTracking(this, "shift");
    },
    // slice could use ARRAY_ITERATE but also seems to beg for range tracking
    some(fn, thisArg) {
      return apply(this, "some", fn, thisArg, void 0, arguments);
    },
    splice(...args) {
      return noTracking(this, "splice", args);
    },
    toReversed() {
      return reactiveReadArray(this).toReversed();
    },
    toSorted(comparer) {
      return reactiveReadArray(this).toSorted(comparer);
    },
    toSpliced(...args) {
      return reactiveReadArray(this).toSpliced(...args);
    },
    unshift(...args) {
      return noTracking(this, "unshift", args);
    },
    values() {
      return iterator(this, "values", toReactive);
    }
  };
  function iterator(self2, method, wrapValue) {
    const arr = shallowReadArray(self2);
    const iter = arr[method]();
    if (arr !== self2 && !isShallow(self2)) {
      iter._next = iter.next;
      iter.next = () => {
        const result = iter._next();
        if (!result.done) {
          result.value = wrapValue(result.value);
        }
        return result;
      };
    }
    return iter;
  }
  const arrayProto = Array.prototype;
  function apply(self2, method, fn, thisArg, wrappedRetFn, args) {
    const arr = shallowReadArray(self2);
    const needsWrap = arr !== self2 && !isShallow(self2);
    const methodFn = arr[method];
    if (methodFn !== arrayProto[method]) {
      const result2 = methodFn.apply(self2, args);
      return needsWrap ? toReactive(result2) : result2;
    }
    let wrappedFn = fn;
    if (arr !== self2) {
      if (needsWrap) {
        wrappedFn = function(item, index) {
          return fn.call(this, toReactive(item), index, self2);
        };
      } else if (fn.length > 2) {
        wrappedFn = function(item, index) {
          return fn.call(this, item, index, self2);
        };
      }
    }
    const result = methodFn.call(arr, wrappedFn, thisArg);
    return needsWrap && wrappedRetFn ? wrappedRetFn(result) : result;
  }
  function reduce(self2, method, fn, args) {
    const arr = shallowReadArray(self2);
    let wrappedFn = fn;
    if (arr !== self2) {
      if (!isShallow(self2)) {
        wrappedFn = function(acc, item, index) {
          return fn.call(this, acc, toReactive(item), index, self2);
        };
      } else if (fn.length > 3) {
        wrappedFn = function(acc, item, index) {
          return fn.call(this, acc, item, index, self2);
        };
      }
    }
    return arr[method](wrappedFn, ...args);
  }
  function searchProxy(self2, method, args) {
    const arr = toRaw(self2);
    track(arr, "iterate", ARRAY_ITERATE_KEY);
    const res = arr[method](...args);
    if ((res === -1 || res === false) && isProxy(args[0])) {
      args[0] = toRaw(args[0]);
      return arr[method](...args);
    }
    return res;
  }
  function noTracking(self2, method, args = []) {
    pauseTracking();
    startBatch();
    const res = toRaw(self2)[method].apply(self2, args);
    endBatch();
    resetTracking();
    return res;
  }
  const isNonTrackableKeys = /* @__PURE__ */ makeMap$1(`__proto__,__v_isRef,__isVue`);
  const builtInSymbols = new Set(
    /* @__PURE__ */ Object.getOwnPropertyNames(Symbol).filter((key) => key !== "arguments" && key !== "caller").map((key) => Symbol[key]).filter(isSymbol$1)
  );
  function hasOwnProperty$1(key) {
    if (!isSymbol$1(key))
      key = String(key);
    const obj = toRaw(this);
    track(obj, "has", key);
    return obj.hasOwnProperty(key);
  }
  class BaseReactiveHandler {
    constructor(_isReadonly = false, _isShallow = false) {
      this._isReadonly = _isReadonly;
      this._isShallow = _isShallow;
    }
    get(target, key, receiver) {
      if (key === "__v_skip")
        return target["__v_skip"];
      const isReadonly2 = this._isReadonly, isShallow2 = this._isShallow;
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_isShallow") {
        return isShallow2;
      } else if (key === "__v_raw") {
        if (receiver === (isReadonly2 ? isShallow2 ? shallowReadonlyMap : readonlyMap : isShallow2 ? shallowReactiveMap : reactiveMap).get(target) || // receiver is not the reactive proxy, but has the same prototype
        // this means the receiver is a user proxy of the reactive proxy
        Object.getPrototypeOf(target) === Object.getPrototypeOf(receiver)) {
          return target;
        }
        return;
      }
      const targetIsArray = isArray$1(target);
      if (!isReadonly2) {
        let fn;
        if (targetIsArray && (fn = arrayInstrumentations[key])) {
          return fn;
        }
        if (key === "hasOwnProperty") {
          return hasOwnProperty$1;
        }
      }
      const res = Reflect.get(
        target,
        key,
        // if this is a proxy wrapping a ref, return methods using the raw ref
        // as receiver so that we don't have to call `toRaw` on the ref in all
        // its class methods
        isRef(target) ? target : receiver
      );
      if (isSymbol$1(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
        return res;
      }
      if (!isReadonly2) {
        track(target, "get", key);
      }
      if (isShallow2) {
        return res;
      }
      if (isRef(res)) {
        const value = targetIsArray && isIntegerKey(key) ? res : res.value;
        return isReadonly2 && isObject(value) ? readonly(value) : value;
      }
      if (isObject(res)) {
        return isReadonly2 ? readonly(res) : reactive(res);
      }
      return res;
    }
  }
  class MutableReactiveHandler extends BaseReactiveHandler {
    constructor(isShallow2 = false) {
      super(false, isShallow2);
    }
    set(target, key, value, receiver) {
      let oldValue = target[key];
      if (!this._isShallow) {
        const isOldValueReadonly = isReadonly(oldValue);
        if (!isShallow(value) && !isReadonly(value)) {
          oldValue = toRaw(oldValue);
          value = toRaw(value);
        }
        if (!isArray$1(target) && isRef(oldValue) && !isRef(value)) {
          if (isOldValueReadonly) {
            return true;
          } else {
            oldValue.value = value;
            return true;
          }
        }
      }
      const hadKey = isArray$1(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn$1(target, key);
      const result = Reflect.set(
        target,
        key,
        value,
        isRef(target) ? target : receiver
      );
      if (target === toRaw(receiver)) {
        if (!hadKey) {
          trigger(target, "add", key, value);
        } else if (hasChanged(value, oldValue)) {
          trigger(target, "set", key, value);
        }
      }
      return result;
    }
    deleteProperty(target, key) {
      const hadKey = hasOwn$1(target, key);
      target[key];
      const result = Reflect.deleteProperty(target, key);
      if (result && hadKey) {
        trigger(target, "delete", key, void 0);
      }
      return result;
    }
    has(target, key) {
      const result = Reflect.has(target, key);
      if (!isSymbol$1(key) || !builtInSymbols.has(key)) {
        track(target, "has", key);
      }
      return result;
    }
    ownKeys(target) {
      track(
        target,
        "iterate",
        isArray$1(target) ? "length" : ITERATE_KEY
      );
      return Reflect.ownKeys(target);
    }
  }
  class ReadonlyReactiveHandler extends BaseReactiveHandler {
    constructor(isShallow2 = false) {
      super(true, isShallow2);
    }
    set(target, key) {
      return true;
    }
    deleteProperty(target, key) {
      return true;
    }
  }
  const mutableHandlers = /* @__PURE__ */ new MutableReactiveHandler();
  const readonlyHandlers = /* @__PURE__ */ new ReadonlyReactiveHandler();
  const shallowReactiveHandlers = /* @__PURE__ */ new MutableReactiveHandler(true);
  const shallowReadonlyHandlers = /* @__PURE__ */ new ReadonlyReactiveHandler(true);
  const toShallow = (value) => value;
  const getProto = (v) => Reflect.getPrototypeOf(v);
  function createIterableMethod(method, isReadonly2, isShallow2) {
    return function(...args) {
      const target = this["__v_raw"];
      const rawTarget = toRaw(target);
      const targetIsMap = isMap(rawTarget);
      const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
      const isKeyOnly = method === "keys" && targetIsMap;
      const innerIterator = target[method](...args);
      const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
      !isReadonly2 && track(
        rawTarget,
        "iterate",
        isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY
      );
      return {
        // iterator protocol
        next() {
          const { value, done } = innerIterator.next();
          return done ? { value, done } : {
            value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
            done
          };
        },
        // iterable protocol
        [Symbol.iterator]() {
          return this;
        }
      };
    };
  }
  function createReadonlyMethod(type) {
    return function(...args) {
      return type === "delete" ? false : type === "clear" ? void 0 : this;
    };
  }
  function createInstrumentations(readonly2, shallow) {
    const instrumentations = {
      get(key) {
        const target = this["__v_raw"];
        const rawTarget = toRaw(target);
        const rawKey = toRaw(key);
        if (!readonly2) {
          if (hasChanged(key, rawKey)) {
            track(rawTarget, "get", key);
          }
          track(rawTarget, "get", rawKey);
        }
        const { has } = getProto(rawTarget);
        const wrap = shallow ? toShallow : readonly2 ? toReadonly : toReactive;
        if (has.call(rawTarget, key)) {
          return wrap(target.get(key));
        } else if (has.call(rawTarget, rawKey)) {
          return wrap(target.get(rawKey));
        } else if (target !== rawTarget) {
          target.get(key);
        }
      },
      get size() {
        const target = this["__v_raw"];
        !readonly2 && track(toRaw(target), "iterate", ITERATE_KEY);
        return target.size;
      },
      has(key) {
        const target = this["__v_raw"];
        const rawTarget = toRaw(target);
        const rawKey = toRaw(key);
        if (!readonly2) {
          if (hasChanged(key, rawKey)) {
            track(rawTarget, "has", key);
          }
          track(rawTarget, "has", rawKey);
        }
        return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
      },
      forEach(callback, thisArg) {
        const observed = this;
        const target = observed["__v_raw"];
        const rawTarget = toRaw(target);
        const wrap = shallow ? toShallow : readonly2 ? toReadonly : toReactive;
        !readonly2 && track(rawTarget, "iterate", ITERATE_KEY);
        return target.forEach((value, key) => {
          return callback.call(thisArg, wrap(value), wrap(key), observed);
        });
      }
    };
    extend$1(
      instrumentations,
      readonly2 ? {
        add: createReadonlyMethod("add"),
        set: createReadonlyMethod("set"),
        delete: createReadonlyMethod("delete"),
        clear: createReadonlyMethod("clear")
      } : {
        add(value) {
          if (!shallow && !isShallow(value) && !isReadonly(value)) {
            value = toRaw(value);
          }
          const target = toRaw(this);
          const proto = getProto(target);
          const hadKey = proto.has.call(target, value);
          if (!hadKey) {
            target.add(value);
            trigger(target, "add", value, value);
          }
          return this;
        },
        set(key, value) {
          if (!shallow && !isShallow(value) && !isReadonly(value)) {
            value = toRaw(value);
          }
          const target = toRaw(this);
          const { has, get } = getProto(target);
          let hadKey = has.call(target, key);
          if (!hadKey) {
            key = toRaw(key);
            hadKey = has.call(target, key);
          }
          const oldValue = get.call(target, key);
          target.set(key, value);
          if (!hadKey) {
            trigger(target, "add", key, value);
          } else if (hasChanged(value, oldValue)) {
            trigger(target, "set", key, value);
          }
          return this;
        },
        delete(key) {
          const target = toRaw(this);
          const { has, get } = getProto(target);
          let hadKey = has.call(target, key);
          if (!hadKey) {
            key = toRaw(key);
            hadKey = has.call(target, key);
          }
          get ? get.call(target, key) : void 0;
          const result = target.delete(key);
          if (hadKey) {
            trigger(target, "delete", key, void 0);
          }
          return result;
        },
        clear() {
          const target = toRaw(this);
          const hadItems = target.size !== 0;
          const result = target.clear();
          if (hadItems) {
            trigger(
              target,
              "clear",
              void 0,
              void 0
            );
          }
          return result;
        }
      }
    );
    const iteratorMethods = [
      "keys",
      "values",
      "entries",
      Symbol.iterator
    ];
    iteratorMethods.forEach((method) => {
      instrumentations[method] = createIterableMethod(method, readonly2, shallow);
    });
    return instrumentations;
  }
  function createInstrumentationGetter(isReadonly2, shallow) {
    const instrumentations = createInstrumentations(isReadonly2, shallow);
    return (target, key, receiver) => {
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_raw") {
        return target;
      }
      return Reflect.get(
        hasOwn$1(instrumentations, key) && key in target ? instrumentations : target,
        key,
        receiver
      );
    };
  }
  const mutableCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(false, false)
  };
  const shallowCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(false, true)
  };
  const readonlyCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(true, false)
  };
  const shallowReadonlyCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(true, true)
  };
  const reactiveMap = /* @__PURE__ */ new WeakMap();
  const shallowReactiveMap = /* @__PURE__ */ new WeakMap();
  const readonlyMap = /* @__PURE__ */ new WeakMap();
  const shallowReadonlyMap = /* @__PURE__ */ new WeakMap();
  function targetTypeMap(rawType) {
    switch (rawType) {
      case "Object":
      case "Array":
        return 1;
      case "Map":
      case "Set":
      case "WeakMap":
      case "WeakSet":
        return 2;
      default:
        return 0;
    }
  }
  function getTargetType(value) {
    return value["__v_skip"] || !Object.isExtensible(value) ? 0 : targetTypeMap(toRawType(value));
  }
  function reactive(target) {
    if (isReadonly(target)) {
      return target;
    }
    return createReactiveObject(
      target,
      false,
      mutableHandlers,
      mutableCollectionHandlers,
      reactiveMap
    );
  }
  function shallowReactive(target) {
    return createReactiveObject(
      target,
      false,
      shallowReactiveHandlers,
      shallowCollectionHandlers,
      shallowReactiveMap
    );
  }
  function readonly(target) {
    return createReactiveObject(
      target,
      true,
      readonlyHandlers,
      readonlyCollectionHandlers,
      readonlyMap
    );
  }
  function shallowReadonly(target) {
    return createReactiveObject(
      target,
      true,
      shallowReadonlyHandlers,
      shallowReadonlyCollectionHandlers,
      shallowReadonlyMap
    );
  }
  function createReactiveObject(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject(target)) {
      return target;
    }
    if (target["__v_raw"] && !(isReadonly2 && target["__v_isReactive"])) {
      return target;
    }
    const targetType = getTargetType(target);
    if (targetType === 0) {
      return target;
    }
    const existingProxy = proxyMap.get(target);
    if (existingProxy) {
      return existingProxy;
    }
    const proxy = new Proxy(
      target,
      targetType === 2 ? collectionHandlers : baseHandlers
    );
    proxyMap.set(target, proxy);
    return proxy;
  }
  function isReactive(value) {
    if (isReadonly(value)) {
      return isReactive(value["__v_raw"]);
    }
    return !!(value && value["__v_isReactive"]);
  }
  function isReadonly(value) {
    return !!(value && value["__v_isReadonly"]);
  }
  function isShallow(value) {
    return !!(value && value["__v_isShallow"]);
  }
  function isProxy(value) {
    return value ? !!value["__v_raw"] : false;
  }
  function toRaw(observed) {
    const raw = observed && observed["__v_raw"];
    return raw ? toRaw(raw) : observed;
  }
  function markRaw(value) {
    if (!hasOwn$1(value, "__v_skip") && Object.isExtensible(value)) {
      def(value, "__v_skip", true);
    }
    return value;
  }
  const toReactive = (value) => isObject(value) ? reactive(value) : value;
  const toReadonly = (value) => isObject(value) ? readonly(value) : value;
  function isRef(r) {
    return r ? r["__v_isRef"] === true : false;
  }
  function ref(value) {
    return createRef(value, false);
  }
  function createRef(rawValue, shallow) {
    if (isRef(rawValue)) {
      return rawValue;
    }
    return new RefImpl(rawValue, shallow);
  }
  class RefImpl {
    constructor(value, isShallow2) {
      this.dep = new Dep();
      this["__v_isRef"] = true;
      this["__v_isShallow"] = false;
      this._rawValue = isShallow2 ? value : toRaw(value);
      this._value = isShallow2 ? value : toReactive(value);
      this["__v_isShallow"] = isShallow2;
    }
    get value() {
      {
        this.dep.track();
      }
      return this._value;
    }
    set value(newValue) {
      const oldValue = this._rawValue;
      const useDirectValue = this["__v_isShallow"] || isShallow(newValue) || isReadonly(newValue);
      newValue = useDirectValue ? newValue : toRaw(newValue);
      if (hasChanged(newValue, oldValue)) {
        this._rawValue = newValue;
        this._value = useDirectValue ? newValue : toReactive(newValue);
        {
          this.dep.trigger();
        }
      }
    }
  }
  function unref(ref2) {
    return isRef(ref2) ? ref2.value : ref2;
  }
  const shallowUnwrapHandlers = {
    get: (target, key, receiver) => key === "__v_raw" ? target : unref(Reflect.get(target, key, receiver)),
    set: (target, key, value, receiver) => {
      const oldValue = target[key];
      if (isRef(oldValue) && !isRef(value)) {
        oldValue.value = value;
        return true;
      } else {
        return Reflect.set(target, key, value, receiver);
      }
    }
  };
  function proxyRefs(objectWithRefs) {
    return isReactive(objectWithRefs) ? objectWithRefs : new Proxy(objectWithRefs, shallowUnwrapHandlers);
  }
  class ComputedRefImpl {
    constructor(fn, setter, isSSR) {
      this.fn = fn;
      this.setter = setter;
      this._value = void 0;
      this.dep = new Dep(this);
      this.__v_isRef = true;
      this.deps = void 0;
      this.depsTail = void 0;
      this.flags = 16;
      this.globalVersion = globalVersion - 1;
      this.next = void 0;
      this.effect = this;
      this["__v_isReadonly"] = !setter;
      this.isSSR = isSSR;
    }
    /**
     * @internal
     */
    notify() {
      this.flags |= 16;
      if (!(this.flags & 8) && // avoid infinite self recursion
      activeSub !== this) {
        batch(this, true);
        return true;
      }
    }
    get value() {
      const link = this.dep.track();
      refreshComputed(this);
      if (link) {
        link.version = this.dep.version;
      }
      return this._value;
    }
    set value(newValue) {
      if (this.setter) {
        this.setter(newValue);
      }
    }
  }
  function computed$1(getterOrOptions, debugOptions, isSSR = false) {
    let getter;
    let setter;
    if (isFunction$1(getterOrOptions)) {
      getter = getterOrOptions;
    } else {
      getter = getterOrOptions.get;
      setter = getterOrOptions.set;
    }
    const cRef = new ComputedRefImpl(getter, setter, isSSR);
    return cRef;
  }
  const INITIAL_WATCHER_VALUE = {};
  const cleanupMap = /* @__PURE__ */ new WeakMap();
  let activeWatcher = void 0;
  function onWatcherCleanup(cleanupFn, failSilently = false, owner = activeWatcher) {
    if (owner) {
      let cleanups = cleanupMap.get(owner);
      if (!cleanups)
        cleanupMap.set(owner, cleanups = []);
      cleanups.push(cleanupFn);
    }
  }
  function watch$1(source, cb, options = EMPTY_OBJ) {
    const { immediate, deep, once, scheduler, augmentJob, call } = options;
    const reactiveGetter = (source2) => {
      if (deep)
        return source2;
      if (isShallow(source2) || deep === false || deep === 0)
        return traverse(source2, 1);
      return traverse(source2);
    };
    let effect;
    let getter;
    let cleanup;
    let boundCleanup;
    let forceTrigger = false;
    let isMultiSource = false;
    if (isRef(source)) {
      getter = () => source.value;
      forceTrigger = isShallow(source);
    } else if (isReactive(source)) {
      getter = () => reactiveGetter(source);
      forceTrigger = true;
    } else if (isArray$1(source)) {
      isMultiSource = true;
      forceTrigger = source.some((s) => isReactive(s) || isShallow(s));
      getter = () => source.map((s) => {
        if (isRef(s)) {
          return s.value;
        } else if (isReactive(s)) {
          return reactiveGetter(s);
        } else if (isFunction$1(s)) {
          return call ? call(s, 2) : s();
        } else
          ;
      });
    } else if (isFunction$1(source)) {
      if (cb) {
        getter = call ? () => call(source, 2) : source;
      } else {
        getter = () => {
          if (cleanup) {
            pauseTracking();
            try {
              cleanup();
            } finally {
              resetTracking();
            }
          }
          const currentEffect = activeWatcher;
          activeWatcher = effect;
          try {
            return call ? call(source, 3, [boundCleanup]) : source(boundCleanup);
          } finally {
            activeWatcher = currentEffect;
          }
        };
      }
    } else {
      getter = NOOP;
    }
    if (cb && deep) {
      const baseGetter = getter;
      const depth = deep === true ? Infinity : deep;
      getter = () => traverse(baseGetter(), depth);
    }
    const scope = getCurrentScope();
    const watchHandle = () => {
      effect.stop();
      if (scope && scope.active) {
        remove(scope.effects, effect);
      }
    };
    if (once && cb) {
      const _cb = cb;
      cb = (...args) => {
        _cb(...args);
        watchHandle();
      };
    }
    let oldValue = isMultiSource ? new Array(source.length).fill(INITIAL_WATCHER_VALUE) : INITIAL_WATCHER_VALUE;
    const job = (immediateFirstRun) => {
      if (!(effect.flags & 1) || !effect.dirty && !immediateFirstRun) {
        return;
      }
      if (cb) {
        const newValue = effect.run();
        if (deep || forceTrigger || (isMultiSource ? newValue.some((v, i) => hasChanged(v, oldValue[i])) : hasChanged(newValue, oldValue))) {
          if (cleanup) {
            cleanup();
          }
          const currentWatcher = activeWatcher;
          activeWatcher = effect;
          try {
            const args = [
              newValue,
              // pass undefined as the old value when it's changed for the first time
              oldValue === INITIAL_WATCHER_VALUE ? void 0 : isMultiSource && oldValue[0] === INITIAL_WATCHER_VALUE ? [] : oldValue,
              boundCleanup
            ];
            oldValue = newValue;
            call ? call(cb, 3, args) : (
              // @ts-expect-error
              cb(...args)
            );
          } finally {
            activeWatcher = currentWatcher;
          }
        }
      } else {
        effect.run();
      }
    };
    if (augmentJob) {
      augmentJob(job);
    }
    effect = new ReactiveEffect(getter);
    effect.scheduler = scheduler ? () => scheduler(job, false) : job;
    boundCleanup = (fn) => onWatcherCleanup(fn, false, effect);
    cleanup = effect.onStop = () => {
      const cleanups = cleanupMap.get(effect);
      if (cleanups) {
        if (call) {
          call(cleanups, 4);
        } else {
          for (const cleanup2 of cleanups)
            cleanup2();
        }
        cleanupMap.delete(effect);
      }
    };
    if (cb) {
      if (immediate) {
        job(true);
      } else {
        oldValue = effect.run();
      }
    } else if (scheduler) {
      scheduler(job.bind(null, true), true);
    } else {
      effect.run();
    }
    watchHandle.pause = effect.pause.bind(effect);
    watchHandle.resume = effect.resume.bind(effect);
    watchHandle.stop = watchHandle;
    return watchHandle;
  }
  function traverse(value, depth = Infinity, seen) {
    if (depth <= 0 || !isObject(value) || value["__v_skip"]) {
      return value;
    }
    seen = seen || /* @__PURE__ */ new Map();
    if ((seen.get(value) || 0) >= depth) {
      return value;
    }
    seen.set(value, depth);
    depth--;
    if (isRef(value)) {
      traverse(value.value, depth, seen);
    } else if (isArray$1(value)) {
      for (let i = 0; i < value.length; i++) {
        traverse(value[i], depth, seen);
      }
    } else if (isSet(value) || isMap(value)) {
      value.forEach((v) => {
        traverse(v, depth, seen);
      });
    } else if (isPlainObject$1(value)) {
      for (const key in value) {
        traverse(value[key], depth, seen);
      }
      for (const key of Object.getOwnPropertySymbols(value)) {
        if (Object.prototype.propertyIsEnumerable.call(value, key)) {
          traverse(value[key], depth, seen);
        }
      }
    }
    return value;
  }
  /**
  * @vue/runtime-core v3.5.22
  * (c) 2018-present Yuxi (Evan) You and Vue contributors
  * @license MIT
  **/
  const stack = [];
  let isWarning = false;
  function warn$1(msg, ...args) {
    if (isWarning)
      return;
    isWarning = true;
    pauseTracking();
    const instance = stack.length ? stack[stack.length - 1].component : null;
    const appWarnHandler = instance && instance.appContext.config.warnHandler;
    const trace = getComponentTrace();
    if (appWarnHandler) {
      callWithErrorHandling(
        appWarnHandler,
        instance,
        11,
        [
          // eslint-disable-next-line no-restricted-syntax
          msg + args.map((a) => {
            var _a, _b;
            return (_b = (_a = a.toString) == null ? void 0 : _a.call(a)) != null ? _b : JSON.stringify(a);
          }).join(""),
          instance && instance.proxy,
          trace.map(
            ({ vnode }) => `at <${formatComponentName(instance, vnode.type)}>`
          ).join("\n"),
          trace
        ]
      );
    } else {
      const warnArgs = [`[Vue warn]: ${msg}`, ...args];
      if (trace.length && // avoid spamming console during tests
      true) {
        warnArgs.push(`
`, ...formatTrace(trace));
      }
      console.warn(...warnArgs);
    }
    resetTracking();
    isWarning = false;
  }
  function getComponentTrace() {
    let currentVNode = stack[stack.length - 1];
    if (!currentVNode) {
      return [];
    }
    const normalizedStack = [];
    while (currentVNode) {
      const last = normalizedStack[0];
      if (last && last.vnode === currentVNode) {
        last.recurseCount++;
      } else {
        normalizedStack.push({
          vnode: currentVNode,
          recurseCount: 0
        });
      }
      const parentInstance = currentVNode.component && currentVNode.component.parent;
      currentVNode = parentInstance && parentInstance.vnode;
    }
    return normalizedStack;
  }
  function formatTrace(trace) {
    const logs = [];
    trace.forEach((entry, i) => {
      logs.push(...i === 0 ? [] : [`
`], ...formatTraceEntry(entry));
    });
    return logs;
  }
  function formatTraceEntry({ vnode, recurseCount }) {
    const postfix = recurseCount > 0 ? `... (${recurseCount} recursive calls)` : ``;
    const isRoot = vnode.component ? vnode.component.parent == null : false;
    const open = ` at <${formatComponentName(
      vnode.component,
      vnode.type,
      isRoot
    )}`;
    const close = `>` + postfix;
    return vnode.props ? [open, ...formatProps(vnode.props), close] : [open + close];
  }
  function formatProps(props) {
    const res = [];
    const keys = Object.keys(props);
    keys.slice(0, 3).forEach((key) => {
      res.push(...formatProp(key, props[key]));
    });
    if (keys.length > 3) {
      res.push(` ...`);
    }
    return res;
  }
  function formatProp(key, value, raw) {
    if (isString$1(value)) {
      value = JSON.stringify(value);
      return raw ? value : [`${key}=${value}`];
    } else if (typeof value === "number" || typeof value === "boolean" || value == null) {
      return raw ? value : [`${key}=${value}`];
    } else if (isRef(value)) {
      value = formatProp(key, toRaw(value.value), true);
      return raw ? value : [`${key}=Ref<`, value, `>`];
    } else if (isFunction$1(value)) {
      return [`${key}=fn${value.name ? `<${value.name}>` : ``}`];
    } else {
      value = toRaw(value);
      return raw ? value : [`${key}=`, value];
    }
  }
  function callWithErrorHandling(fn, instance, type, args) {
    try {
      return args ? fn(...args) : fn();
    } catch (err) {
      handleError(err, instance, type);
    }
  }
  function callWithAsyncErrorHandling(fn, instance, type, args) {
    if (isFunction$1(fn)) {
      const res = callWithErrorHandling(fn, instance, type, args);
      if (res && isPromise(res)) {
        res.catch((err) => {
          handleError(err, instance, type);
        });
      }
      return res;
    }
    if (isArray$1(fn)) {
      const values = [];
      for (let i = 0; i < fn.length; i++) {
        values.push(callWithAsyncErrorHandling(fn[i], instance, type, args));
      }
      return values;
    }
  }
  function handleError(err, instance, type, throwInDev = true) {
    const contextVNode = instance ? instance.vnode : null;
    const { errorHandler, throwUnhandledErrorInProduction } = instance && instance.appContext.config || EMPTY_OBJ;
    if (instance) {
      let cur = instance.parent;
      const exposedInstance = instance.proxy;
      const errorInfo = `https://vuejs.org/error-reference/#runtime-${type}`;
      while (cur) {
        const errorCapturedHooks = cur.ec;
        if (errorCapturedHooks) {
          for (let i = 0; i < errorCapturedHooks.length; i++) {
            if (errorCapturedHooks[i](err, exposedInstance, errorInfo) === false) {
              return;
            }
          }
        }
        cur = cur.parent;
      }
      if (errorHandler) {
        pauseTracking();
        callWithErrorHandling(errorHandler, null, 10, [
          err,
          exposedInstance,
          errorInfo
        ]);
        resetTracking();
        return;
      }
    }
    logError(err, type, contextVNode, throwInDev, throwUnhandledErrorInProduction);
  }
  function logError(err, type, contextVNode, throwInDev = true, throwInProd = false) {
    if (throwInProd) {
      throw err;
    } else {
      console.error(err);
    }
  }
  const queue = [];
  let flushIndex = -1;
  const pendingPostFlushCbs = [];
  let activePostFlushCbs = null;
  let postFlushIndex = 0;
  const resolvedPromise = /* @__PURE__ */ Promise.resolve();
  let currentFlushPromise = null;
  function nextTick(fn) {
    const p2 = currentFlushPromise || resolvedPromise;
    return fn ? p2.then(this ? fn.bind(this) : fn) : p2;
  }
  function findInsertionIndex(id) {
    let start = flushIndex + 1;
    let end = queue.length;
    while (start < end) {
      const middle = start + end >>> 1;
      const middleJob = queue[middle];
      const middleJobId = getId(middleJob);
      if (middleJobId < id || middleJobId === id && middleJob.flags & 2) {
        start = middle + 1;
      } else {
        end = middle;
      }
    }
    return start;
  }
  function queueJob(job) {
    if (!(job.flags & 1)) {
      const jobId = getId(job);
      const lastJob = queue[queue.length - 1];
      if (!lastJob || // fast path when the job id is larger than the tail
      !(job.flags & 2) && jobId >= getId(lastJob)) {
        queue.push(job);
      } else {
        queue.splice(findInsertionIndex(jobId), 0, job);
      }
      job.flags |= 1;
      queueFlush();
    }
  }
  function queueFlush() {
    if (!currentFlushPromise) {
      currentFlushPromise = resolvedPromise.then(flushJobs);
    }
  }
  function queuePostFlushCb(cb) {
    if (!isArray$1(cb)) {
      if (activePostFlushCbs && cb.id === -1) {
        activePostFlushCbs.splice(postFlushIndex + 1, 0, cb);
      } else if (!(cb.flags & 1)) {
        pendingPostFlushCbs.push(cb);
        cb.flags |= 1;
      }
    } else {
      pendingPostFlushCbs.push(...cb);
    }
    queueFlush();
  }
  function flushPreFlushCbs(instance, seen, i = flushIndex + 1) {
    for (; i < queue.length; i++) {
      const cb = queue[i];
      if (cb && cb.flags & 2) {
        if (instance && cb.id !== instance.uid) {
          continue;
        }
        queue.splice(i, 1);
        i--;
        if (cb.flags & 4) {
          cb.flags &= -2;
        }
        cb();
        if (!(cb.flags & 4)) {
          cb.flags &= -2;
        }
      }
    }
  }
  function flushPostFlushCbs(seen) {
    if (pendingPostFlushCbs.length) {
      const deduped = [...new Set(pendingPostFlushCbs)].sort(
        (a, b) => getId(a) - getId(b)
      );
      pendingPostFlushCbs.length = 0;
      if (activePostFlushCbs) {
        activePostFlushCbs.push(...deduped);
        return;
      }
      activePostFlushCbs = deduped;
      for (postFlushIndex = 0; postFlushIndex < activePostFlushCbs.length; postFlushIndex++) {
        const cb = activePostFlushCbs[postFlushIndex];
        if (cb.flags & 4) {
          cb.flags &= -2;
        }
        if (!(cb.flags & 8))
          cb();
        cb.flags &= -2;
      }
      activePostFlushCbs = null;
      postFlushIndex = 0;
    }
  }
  const getId = (job) => job.id == null ? job.flags & 2 ? -1 : Infinity : job.id;
  function flushJobs(seen) {
    const check = NOOP;
    try {
      for (flushIndex = 0; flushIndex < queue.length; flushIndex++) {
        const job = queue[flushIndex];
        if (job && !(job.flags & 8)) {
          if (false)
            ;
          if (job.flags & 4) {
            job.flags &= ~1;
          }
          callWithErrorHandling(
            job,
            job.i,
            job.i ? 15 : 14
          );
          if (!(job.flags & 4)) {
            job.flags &= ~1;
          }
        }
      }
    } finally {
      for (; flushIndex < queue.length; flushIndex++) {
        const job = queue[flushIndex];
        if (job) {
          job.flags &= -2;
        }
      }
      flushIndex = -1;
      queue.length = 0;
      flushPostFlushCbs();
      currentFlushPromise = null;
      if (queue.length || pendingPostFlushCbs.length) {
        flushJobs();
      }
    }
  }
  let currentRenderingInstance = null;
  let currentScopeId = null;
  function setCurrentRenderingInstance(instance) {
    const prev = currentRenderingInstance;
    currentRenderingInstance = instance;
    currentScopeId = instance && instance.type.__scopeId || null;
    return prev;
  }
  function withCtx(fn, ctx = currentRenderingInstance, isNonScopedSlot) {
    if (!ctx)
      return fn;
    if (fn._n) {
      return fn;
    }
    const renderFnWithContext = (...args) => {
      if (renderFnWithContext._d) {
        setBlockTracking(-1);
      }
      const prevInstance = setCurrentRenderingInstance(ctx);
      let res;
      try {
        res = fn(...args);
      } finally {
        setCurrentRenderingInstance(prevInstance);
        if (renderFnWithContext._d) {
          setBlockTracking(1);
        }
      }
      return res;
    };
    renderFnWithContext._n = true;
    renderFnWithContext._c = true;
    renderFnWithContext._d = true;
    return renderFnWithContext;
  }
  function invokeDirectiveHook(vnode, prevVNode, instance, name) {
    const bindings = vnode.dirs;
    const oldBindings = prevVNode && prevVNode.dirs;
    for (let i = 0; i < bindings.length; i++) {
      const binding = bindings[i];
      if (oldBindings) {
        binding.oldValue = oldBindings[i].value;
      }
      let hook = binding.dir[name];
      if (hook) {
        pauseTracking();
        callWithAsyncErrorHandling(hook, instance, 8, [
          vnode.el,
          binding,
          vnode,
          prevVNode
        ]);
        resetTracking();
      }
    }
  }
  const TeleportEndKey = Symbol("_vte");
  const isTeleport = (type) => type.__isTeleport;
  const leaveCbKey = Symbol("_leaveCb");
  function setTransitionHooks(vnode, hooks) {
    if (vnode.shapeFlag & 6 && vnode.component) {
      vnode.transition = hooks;
      setTransitionHooks(vnode.component.subTree, hooks);
    } else if (vnode.shapeFlag & 128) {
      vnode.ssContent.transition = hooks.clone(vnode.ssContent);
      vnode.ssFallback.transition = hooks.clone(vnode.ssFallback);
    } else {
      vnode.transition = hooks;
    }
  }
  // @__NO_SIDE_EFFECTS__
  function defineComponent(options, extraOptions) {
    return isFunction$1(options) ? (
      // #8236: extend call and options.name access are considered side-effects
      // by Rollup, so we have to wrap it in a pure-annotated IIFE.
      /* @__PURE__ */ (() => extend$1({ name: options.name }, extraOptions, { setup: options }))()
    ) : options;
  }
  function markAsyncBoundary(instance) {
    instance.ids = [instance.ids[0] + instance.ids[2]++ + "-", 0, 0];
  }
  const pendingSetRefMap = /* @__PURE__ */ new WeakMap();
  function setRef(rawRef, oldRawRef, parentSuspense, vnode, isUnmount = false) {
    if (isArray$1(rawRef)) {
      rawRef.forEach(
        (r, i) => setRef(
          r,
          oldRawRef && (isArray$1(oldRawRef) ? oldRawRef[i] : oldRawRef),
          parentSuspense,
          vnode,
          isUnmount
        )
      );
      return;
    }
    if (isAsyncWrapper(vnode) && !isUnmount) {
      if (vnode.shapeFlag & 512 && vnode.type.__asyncResolved && vnode.component.subTree.component) {
        setRef(rawRef, oldRawRef, parentSuspense, vnode.component.subTree);
      }
      return;
    }
    const refValue = vnode.shapeFlag & 4 ? getComponentPublicInstance(vnode.component) : vnode.el;
    const value = isUnmount ? null : refValue;
    const { i: owner, r: ref2 } = rawRef;
    const oldRef = oldRawRef && oldRawRef.r;
    const refs = owner.refs === EMPTY_OBJ ? owner.refs = {} : owner.refs;
    const setupState = owner.setupState;
    const rawSetupState = toRaw(setupState);
    const canSetSetupRef = setupState === EMPTY_OBJ ? NO : (key) => {
      return hasOwn$1(rawSetupState, key);
    };
    if (oldRef != null && oldRef !== ref2) {
      invalidatePendingSetRef(oldRawRef);
      if (isString$1(oldRef)) {
        refs[oldRef] = null;
        if (canSetSetupRef(oldRef)) {
          setupState[oldRef] = null;
        }
      } else if (isRef(oldRef)) {
        {
          oldRef.value = null;
        }
        const oldRawRefAtom = oldRawRef;
        if (oldRawRefAtom.k)
          refs[oldRawRefAtom.k] = null;
      }
    }
    if (isFunction$1(ref2)) {
      callWithErrorHandling(ref2, owner, 12, [value, refs]);
    } else {
      const _isString = isString$1(ref2);
      const _isRef = isRef(ref2);
      if (_isString || _isRef) {
        const doSet = () => {
          if (rawRef.f) {
            const existing = _isString ? canSetSetupRef(ref2) ? setupState[ref2] : refs[ref2] : ref2.value;
            if (isUnmount) {
              isArray$1(existing) && remove(existing, refValue);
            } else {
              if (!isArray$1(existing)) {
                if (_isString) {
                  refs[ref2] = [refValue];
                  if (canSetSetupRef(ref2)) {
                    setupState[ref2] = refs[ref2];
                  }
                } else {
                  const newVal = [refValue];
                  {
                    ref2.value = newVal;
                  }
                  if (rawRef.k)
                    refs[rawRef.k] = newVal;
                }
              } else if (!existing.includes(refValue)) {
                existing.push(refValue);
              }
            }
          } else if (_isString) {
            refs[ref2] = value;
            if (canSetSetupRef(ref2)) {
              setupState[ref2] = value;
            }
          } else if (_isRef) {
            {
              ref2.value = value;
            }
            if (rawRef.k)
              refs[rawRef.k] = value;
          } else
            ;
        };
        if (value) {
          const job = () => {
            doSet();
            pendingSetRefMap.delete(rawRef);
          };
          job.id = -1;
          pendingSetRefMap.set(rawRef, job);
          queuePostRenderEffect(job, parentSuspense);
        } else {
          invalidatePendingSetRef(rawRef);
          doSet();
        }
      }
    }
  }
  function invalidatePendingSetRef(rawRef) {
    const pendingSetRef = pendingSetRefMap.get(rawRef);
    if (pendingSetRef) {
      pendingSetRef.flags |= 8;
      pendingSetRefMap.delete(rawRef);
    }
  }
  getGlobalThis().requestIdleCallback || ((cb) => setTimeout(cb, 1));
  getGlobalThis().cancelIdleCallback || ((id) => clearTimeout(id));
  const isAsyncWrapper = (i) => !!i.type.__asyncLoader;
  const isKeepAlive = (vnode) => vnode.type.__isKeepAlive;
  function onActivated(hook, target) {
    registerKeepAliveHook(hook, "a", target);
  }
  function onDeactivated(hook, target) {
    registerKeepAliveHook(hook, "da", target);
  }
  function registerKeepAliveHook(hook, type, target = currentInstance) {
    const wrappedHook = hook.__wdc || (hook.__wdc = () => {
      let current = target;
      while (current) {
        if (current.isDeactivated) {
          return;
        }
        current = current.parent;
      }
      return hook();
    });
    injectHook(type, wrappedHook, target);
    if (target) {
      let current = target.parent;
      while (current && current.parent) {
        if (isKeepAlive(current.parent.vnode)) {
          injectToKeepAliveRoot(wrappedHook, type, target, current);
        }
        current = current.parent;
      }
    }
  }
  function injectToKeepAliveRoot(hook, type, target, keepAliveRoot) {
    const injected = injectHook(
      type,
      hook,
      keepAliveRoot,
      true
      /* prepend */
    );
    onUnmounted(() => {
      remove(keepAliveRoot[type], injected);
    }, target);
  }
  function injectHook(type, hook, target = currentInstance, prepend = false) {
    if (target) {
      const hooks = target[type] || (target[type] = []);
      const wrappedHook = hook.__weh || (hook.__weh = (...args) => {
        pauseTracking();
        const reset = setCurrentInstance(target);
        const res = callWithAsyncErrorHandling(hook, target, type, args);
        reset();
        resetTracking();
        return res;
      });
      if (prepend) {
        hooks.unshift(wrappedHook);
      } else {
        hooks.push(wrappedHook);
      }
      return wrappedHook;
    }
  }
  const createHook = (lifecycle) => (hook, target = currentInstance) => {
    if (!isInSSRComponentSetup || lifecycle === "sp") {
      injectHook(lifecycle, (...args) => hook(...args), target);
    }
  };
  const onBeforeMount = createHook("bm");
  const onMounted = createHook("m");
  const onBeforeUpdate = createHook(
    "bu"
  );
  const onUpdated = createHook("u");
  const onBeforeUnmount = createHook(
    "bum"
  );
  const onUnmounted = createHook("um");
  const onServerPrefetch = createHook(
    "sp"
  );
  const onRenderTriggered = createHook("rtg");
  const onRenderTracked = createHook("rtc");
  function onErrorCaptured(hook, target = currentInstance) {
    injectHook("ec", hook, target);
  }
  const NULL_DYNAMIC_COMPONENT = Symbol.for("v-ndc");
  const getPublicInstance = (i) => {
    if (!i)
      return null;
    if (isStatefulComponent(i))
      return getComponentPublicInstance(i);
    return getPublicInstance(i.parent);
  };
  const publicPropertiesMap = (
    // Move PURE marker to new line to workaround compiler discarding it
    // due to type annotation
    /* @__PURE__ */ extend$1(/* @__PURE__ */ Object.create(null), {
      $: (i) => i,
      $el: (i) => i.vnode.el,
      $data: (i) => i.data,
      $props: (i) => i.props,
      $attrs: (i) => i.attrs,
      $slots: (i) => i.slots,
      $refs: (i) => i.refs,
      $parent: (i) => getPublicInstance(i.parent),
      $root: (i) => getPublicInstance(i.root),
      $host: (i) => i.ce,
      $emit: (i) => i.emit,
      $options: (i) => resolveMergedOptions(i),
      $forceUpdate: (i) => i.f || (i.f = () => {
        queueJob(i.update);
      }),
      $nextTick: (i) => i.n || (i.n = nextTick.bind(i.proxy)),
      $watch: (i) => instanceWatch.bind(i)
    })
  );
  const hasSetupBinding = (state, key) => state !== EMPTY_OBJ && !state.__isScriptSetup && hasOwn$1(state, key);
  const PublicInstanceProxyHandlers = {
    get({ _: instance }, key) {
      if (key === "__v_skip") {
        return true;
      }
      const { ctx, setupState, data, props, accessCache, type, appContext } = instance;
      let normalizedProps;
      if (key[0] !== "$") {
        const n = accessCache[key];
        if (n !== void 0) {
          switch (n) {
            case 1:
              return setupState[key];
            case 2:
              return data[key];
            case 4:
              return ctx[key];
            case 3:
              return props[key];
          }
        } else if (hasSetupBinding(setupState, key)) {
          accessCache[key] = 1;
          return setupState[key];
        } else if (data !== EMPTY_OBJ && hasOwn$1(data, key)) {
          accessCache[key] = 2;
          return data[key];
        } else if (
          // only cache other properties when instance has declared (thus stable)
          // props
          (normalizedProps = instance.propsOptions[0]) && hasOwn$1(normalizedProps, key)
        ) {
          accessCache[key] = 3;
          return props[key];
        } else if (ctx !== EMPTY_OBJ && hasOwn$1(ctx, key)) {
          accessCache[key] = 4;
          return ctx[key];
        } else if (shouldCacheAccess) {
          accessCache[key] = 0;
        }
      }
      const publicGetter = publicPropertiesMap[key];
      let cssModule, globalProperties;
      if (publicGetter) {
        if (key === "$attrs") {
          track(instance.attrs, "get", "");
        }
        return publicGetter(instance);
      } else if (
        // css module (injected by vue-loader)
        (cssModule = type.__cssModules) && (cssModule = cssModule[key])
      ) {
        return cssModule;
      } else if (ctx !== EMPTY_OBJ && hasOwn$1(ctx, key)) {
        accessCache[key] = 4;
        return ctx[key];
      } else if (
        // global properties
        globalProperties = appContext.config.globalProperties, hasOwn$1(globalProperties, key)
      ) {
        {
          return globalProperties[key];
        }
      } else
        ;
    },
    set({ _: instance }, key, value) {
      const { data, setupState, ctx } = instance;
      if (hasSetupBinding(setupState, key)) {
        setupState[key] = value;
        return true;
      } else if (data !== EMPTY_OBJ && hasOwn$1(data, key)) {
        data[key] = value;
        return true;
      } else if (hasOwn$1(instance.props, key)) {
        return false;
      }
      if (key[0] === "$" && key.slice(1) in instance) {
        return false;
      } else {
        {
          ctx[key] = value;
        }
      }
      return true;
    },
    has({
      _: { data, setupState, accessCache, ctx, appContext, propsOptions, type }
    }, key) {
      let normalizedProps, cssModules;
      return !!(accessCache[key] || data !== EMPTY_OBJ && key[0] !== "$" && hasOwn$1(data, key) || hasSetupBinding(setupState, key) || (normalizedProps = propsOptions[0]) && hasOwn$1(normalizedProps, key) || hasOwn$1(ctx, key) || hasOwn$1(publicPropertiesMap, key) || hasOwn$1(appContext.config.globalProperties, key) || (cssModules = type.__cssModules) && cssModules[key]);
    },
    defineProperty(target, key, descriptor) {
      if (descriptor.get != null) {
        target._.accessCache[key] = 0;
      } else if (hasOwn$1(descriptor, "value")) {
        this.set(target, key, descriptor.value, null);
      }
      return Reflect.defineProperty(target, key, descriptor);
    }
  };
  function normalizePropsOrEmits(props) {
    return isArray$1(props) ? props.reduce(
      (normalized, p2) => (normalized[p2] = null, normalized),
      {}
    ) : props;
  }
  let shouldCacheAccess = true;
  function applyOptions(instance) {
    const options = resolveMergedOptions(instance);
    const publicThis = instance.proxy;
    const ctx = instance.ctx;
    shouldCacheAccess = false;
    if (options.beforeCreate) {
      callHook(options.beforeCreate, instance, "bc");
    }
    const {
      // state
      data: dataOptions,
      computed: computedOptions,
      methods,
      watch: watchOptions,
      provide: provideOptions,
      inject: injectOptions,
      // lifecycle
      created,
      beforeMount,
      mounted,
      beforeUpdate,
      updated,
      activated,
      deactivated,
      beforeDestroy,
      beforeUnmount,
      destroyed,
      unmounted,
      render: render2,
      renderTracked,
      renderTriggered,
      errorCaptured,
      serverPrefetch,
      // public API
      expose,
      inheritAttrs,
      // assets
      components,
      directives,
      filters
    } = options;
    const checkDuplicateProperties = null;
    if (injectOptions) {
      resolveInjections(injectOptions, ctx, checkDuplicateProperties);
    }
    if (methods) {
      for (const key in methods) {
        const methodHandler = methods[key];
        if (isFunction$1(methodHandler)) {
          {
            ctx[key] = methodHandler.bind(publicThis);
          }
        }
      }
    }
    if (dataOptions) {
      const data = dataOptions.call(publicThis, publicThis);
      if (!isObject(data))
        ;
      else {
        instance.data = reactive(data);
      }
    }
    shouldCacheAccess = true;
    if (computedOptions) {
      for (const key in computedOptions) {
        const opt = computedOptions[key];
        const get = isFunction$1(opt) ? opt.bind(publicThis, publicThis) : isFunction$1(opt.get) ? opt.get.bind(publicThis, publicThis) : NOOP;
        const set = !isFunction$1(opt) && isFunction$1(opt.set) ? opt.set.bind(publicThis) : NOOP;
        const c = computed({
          get,
          set
        });
        Object.defineProperty(ctx, key, {
          enumerable: true,
          configurable: true,
          get: () => c.value,
          set: (v) => c.value = v
        });
      }
    }
    if (watchOptions) {
      for (const key in watchOptions) {
        createWatcher(watchOptions[key], ctx, publicThis, key);
      }
    }
    if (provideOptions) {
      const provides = isFunction$1(provideOptions) ? provideOptions.call(publicThis) : provideOptions;
      Reflect.ownKeys(provides).forEach((key) => {
        provide(key, provides[key]);
      });
    }
    if (created) {
      callHook(created, instance, "c");
    }
    function registerLifecycleHook(register, hook) {
      if (isArray$1(hook)) {
        hook.forEach((_hook) => register(_hook.bind(publicThis)));
      } else if (hook) {
        register(hook.bind(publicThis));
      }
    }
    registerLifecycleHook(onBeforeMount, beforeMount);
    registerLifecycleHook(onMounted, mounted);
    registerLifecycleHook(onBeforeUpdate, beforeUpdate);
    registerLifecycleHook(onUpdated, updated);
    registerLifecycleHook(onActivated, activated);
    registerLifecycleHook(onDeactivated, deactivated);
    registerLifecycleHook(onErrorCaptured, errorCaptured);
    registerLifecycleHook(onRenderTracked, renderTracked);
    registerLifecycleHook(onRenderTriggered, renderTriggered);
    registerLifecycleHook(onBeforeUnmount, beforeUnmount);
    registerLifecycleHook(onUnmounted, unmounted);
    registerLifecycleHook(onServerPrefetch, serverPrefetch);
    if (isArray$1(expose)) {
      if (expose.length) {
        const exposed = instance.exposed || (instance.exposed = {});
        expose.forEach((key) => {
          Object.defineProperty(exposed, key, {
            get: () => publicThis[key],
            set: (val) => publicThis[key] = val,
            enumerable: true
          });
        });
      } else if (!instance.exposed) {
        instance.exposed = {};
      }
    }
    if (render2 && instance.render === NOOP) {
      instance.render = render2;
    }
    if (inheritAttrs != null) {
      instance.inheritAttrs = inheritAttrs;
    }
    if (components)
      instance.components = components;
    if (directives)
      instance.directives = directives;
    if (serverPrefetch) {
      markAsyncBoundary(instance);
    }
  }
  function resolveInjections(injectOptions, ctx, checkDuplicateProperties = NOOP) {
    if (isArray$1(injectOptions)) {
      injectOptions = normalizeInject(injectOptions);
    }
    for (const key in injectOptions) {
      const opt = injectOptions[key];
      let injected;
      if (isObject(opt)) {
        if ("default" in opt) {
          injected = inject(
            opt.from || key,
            opt.default,
            true
          );
        } else {
          injected = inject(opt.from || key);
        }
      } else {
        injected = inject(opt);
      }
      if (isRef(injected)) {
        Object.defineProperty(ctx, key, {
          enumerable: true,
          configurable: true,
          get: () => injected.value,
          set: (v) => injected.value = v
        });
      } else {
        ctx[key] = injected;
      }
    }
  }
  function callHook(hook, instance, type) {
    callWithAsyncErrorHandling(
      isArray$1(hook) ? hook.map((h) => h.bind(instance.proxy)) : hook.bind(instance.proxy),
      instance,
      type
    );
  }
  function createWatcher(raw, ctx, publicThis, key) {
    let getter = key.includes(".") ? createPathGetter(publicThis, key) : () => publicThis[key];
    if (isString$1(raw)) {
      const handler = ctx[raw];
      if (isFunction$1(handler)) {
        {
          watch(getter, handler);
        }
      }
    } else if (isFunction$1(raw)) {
      {
        watch(getter, raw.bind(publicThis));
      }
    } else if (isObject(raw)) {
      if (isArray$1(raw)) {
        raw.forEach((r) => createWatcher(r, ctx, publicThis, key));
      } else {
        const handler = isFunction$1(raw.handler) ? raw.handler.bind(publicThis) : ctx[raw.handler];
        if (isFunction$1(handler)) {
          watch(getter, handler, raw);
        }
      }
    } else
      ;
  }
  function resolveMergedOptions(instance) {
    const base = instance.type;
    const { mixins, extends: extendsOptions } = base;
    const {
      mixins: globalMixins,
      optionsCache: cache,
      config: { optionMergeStrategies }
    } = instance.appContext;
    const cached = cache.get(base);
    let resolved;
    if (cached) {
      resolved = cached;
    } else if (!globalMixins.length && !mixins && !extendsOptions) {
      {
        resolved = base;
      }
    } else {
      resolved = {};
      if (globalMixins.length) {
        globalMixins.forEach(
          (m) => mergeOptions(resolved, m, optionMergeStrategies, true)
        );
      }
      mergeOptions(resolved, base, optionMergeStrategies);
    }
    if (isObject(base)) {
      cache.set(base, resolved);
    }
    return resolved;
  }
  function mergeOptions(to, from, strats, asMixin = false) {
    const { mixins, extends: extendsOptions } = from;
    if (extendsOptions) {
      mergeOptions(to, extendsOptions, strats, true);
    }
    if (mixins) {
      mixins.forEach(
        (m) => mergeOptions(to, m, strats, true)
      );
    }
    for (const key in from) {
      if (asMixin && key === "expose")
        ;
      else {
        const strat = internalOptionMergeStrats[key] || strats && strats[key];
        to[key] = strat ? strat(to[key], from[key]) : from[key];
      }
    }
    return to;
  }
  const internalOptionMergeStrats = {
    data: mergeDataFn,
    props: mergeEmitsOrPropsOptions,
    emits: mergeEmitsOrPropsOptions,
    // objects
    methods: mergeObjectOptions,
    computed: mergeObjectOptions,
    // lifecycle
    beforeCreate: mergeAsArray,
    created: mergeAsArray,
    beforeMount: mergeAsArray,
    mounted: mergeAsArray,
    beforeUpdate: mergeAsArray,
    updated: mergeAsArray,
    beforeDestroy: mergeAsArray,
    beforeUnmount: mergeAsArray,
    destroyed: mergeAsArray,
    unmounted: mergeAsArray,
    activated: mergeAsArray,
    deactivated: mergeAsArray,
    errorCaptured: mergeAsArray,
    serverPrefetch: mergeAsArray,
    // assets
    components: mergeObjectOptions,
    directives: mergeObjectOptions,
    // watch
    watch: mergeWatchOptions,
    // provide / inject
    provide: mergeDataFn,
    inject: mergeInject
  };
  function mergeDataFn(to, from) {
    if (!from) {
      return to;
    }
    if (!to) {
      return from;
    }
    return function mergedDataFn() {
      return extend$1(
        isFunction$1(to) ? to.call(this, this) : to,
        isFunction$1(from) ? from.call(this, this) : from
      );
    };
  }
  function mergeInject(to, from) {
    return mergeObjectOptions(normalizeInject(to), normalizeInject(from));
  }
  function normalizeInject(raw) {
    if (isArray$1(raw)) {
      const res = {};
      for (let i = 0; i < raw.length; i++) {
        res[raw[i]] = raw[i];
      }
      return res;
    }
    return raw;
  }
  function mergeAsArray(to, from) {
    return to ? [...new Set([].concat(to, from))] : from;
  }
  function mergeObjectOptions(to, from) {
    return to ? extend$1(/* @__PURE__ */ Object.create(null), to, from) : from;
  }
  function mergeEmitsOrPropsOptions(to, from) {
    if (to) {
      if (isArray$1(to) && isArray$1(from)) {
        return [.../* @__PURE__ */ new Set([...to, ...from])];
      }
      return extend$1(
        /* @__PURE__ */ Object.create(null),
        normalizePropsOrEmits(to),
        normalizePropsOrEmits(from != null ? from : {})
      );
    } else {
      return from;
    }
  }
  function mergeWatchOptions(to, from) {
    if (!to)
      return from;
    if (!from)
      return to;
    const merged = extend$1(/* @__PURE__ */ Object.create(null), to);
    for (const key in from) {
      merged[key] = mergeAsArray(to[key], from[key]);
    }
    return merged;
  }
  function createAppContext() {
    return {
      app: null,
      config: {
        isNativeTag: NO,
        performance: false,
        globalProperties: {},
        optionMergeStrategies: {},
        errorHandler: void 0,
        warnHandler: void 0,
        compilerOptions: {}
      },
      mixins: [],
      components: {},
      directives: {},
      provides: /* @__PURE__ */ Object.create(null),
      optionsCache: /* @__PURE__ */ new WeakMap(),
      propsCache: /* @__PURE__ */ new WeakMap(),
      emitsCache: /* @__PURE__ */ new WeakMap()
    };
  }
  let uid$1 = 0;
  function createAppAPI(render2, hydrate) {
    return function createApp2(rootComponent, rootProps = null) {
      if (!isFunction$1(rootComponent)) {
        rootComponent = extend$1({}, rootComponent);
      }
      if (rootProps != null && !isObject(rootProps)) {
        rootProps = null;
      }
      const context = createAppContext();
      const installedPlugins = /* @__PURE__ */ new WeakSet();
      const pluginCleanupFns = [];
      let isMounted = false;
      const app = context.app = {
        _uid: uid$1++,
        _component: rootComponent,
        _props: rootProps,
        _container: null,
        _context: context,
        _instance: null,
        version,
        get config() {
          return context.config;
        },
        set config(v) {
        },
        use(plugin, ...options) {
          if (installedPlugins.has(plugin))
            ;
          else if (plugin && isFunction$1(plugin.install)) {
            installedPlugins.add(plugin);
            plugin.install(app, ...options);
          } else if (isFunction$1(plugin)) {
            installedPlugins.add(plugin);
            plugin(app, ...options);
          } else
            ;
          return app;
        },
        mixin(mixin) {
          {
            if (!context.mixins.includes(mixin)) {
              context.mixins.push(mixin);
            }
          }
          return app;
        },
        component(name, component) {
          if (!component) {
            return context.components[name];
          }
          context.components[name] = component;
          return app;
        },
        directive(name, directive) {
          if (!directive) {
            return context.directives[name];
          }
          context.directives[name] = directive;
          return app;
        },
        mount(rootContainer, isHydrate, namespace) {
          if (!isMounted) {
            const vnode = app._ceVNode || createVNode(rootComponent, rootProps);
            vnode.appContext = context;
            if (namespace === true) {
              namespace = "svg";
            } else if (namespace === false) {
              namespace = void 0;
            }
            if (isHydrate && hydrate) {
              hydrate(vnode, rootContainer);
            } else {
              render2(vnode, rootContainer, namespace);
            }
            isMounted = true;
            app._container = rootContainer;
            rootContainer.__vue_app__ = app;
            return getComponentPublicInstance(vnode.component);
          }
        },
        onUnmount(cleanupFn) {
          pluginCleanupFns.push(cleanupFn);
        },
        unmount() {
          if (isMounted) {
            callWithAsyncErrorHandling(
              pluginCleanupFns,
              app._instance,
              16
            );
            render2(null, app._container);
            delete app._container.__vue_app__;
          }
        },
        provide(key, value) {
          context.provides[key] = value;
          return app;
        },
        runWithContext(fn) {
          const lastApp = currentApp;
          currentApp = app;
          try {
            return fn();
          } finally {
            currentApp = lastApp;
          }
        }
      };
      return app;
    };
  }
  let currentApp = null;
  function provide(key, value) {
    if (!currentInstance)
      ;
    else {
      let provides = currentInstance.provides;
      const parentProvides = currentInstance.parent && currentInstance.parent.provides;
      if (parentProvides === provides) {
        provides = currentInstance.provides = Object.create(parentProvides);
      }
      provides[key] = value;
    }
  }
  function inject(key, defaultValue, treatDefaultAsFactory = false) {
    const instance = getCurrentInstance();
    if (instance || currentApp) {
      let provides = currentApp ? currentApp._context.provides : instance ? instance.parent == null || instance.ce ? instance.vnode.appContext && instance.vnode.appContext.provides : instance.parent.provides : void 0;
      if (provides && key in provides) {
        return provides[key];
      } else if (arguments.length > 1) {
        return treatDefaultAsFactory && isFunction$1(defaultValue) ? defaultValue.call(instance && instance.proxy) : defaultValue;
      } else
        ;
    }
  }
  const internalObjectProto = {};
  const createInternalObject = () => Object.create(internalObjectProto);
  const isInternalObject = (obj) => Object.getPrototypeOf(obj) === internalObjectProto;
  function initProps(instance, rawProps, isStateful, isSSR = false) {
    const props = {};
    const attrs = createInternalObject();
    instance.propsDefaults = /* @__PURE__ */ Object.create(null);
    setFullProps(instance, rawProps, props, attrs);
    for (const key in instance.propsOptions[0]) {
      if (!(key in props)) {
        props[key] = void 0;
      }
    }
    if (isStateful) {
      instance.props = isSSR ? props : shallowReactive(props);
    } else {
      if (!instance.type.props) {
        instance.props = attrs;
      } else {
        instance.props = props;
      }
    }
    instance.attrs = attrs;
  }
  function updateProps(instance, rawProps, rawPrevProps, optimized) {
    const {
      props,
      attrs,
      vnode: { patchFlag }
    } = instance;
    const rawCurrentProps = toRaw(props);
    const [options] = instance.propsOptions;
    let hasAttrsChanged = false;
    if (
      // always force full diff in dev
      // - #1942 if hmr is enabled with sfc component
      // - vite#872 non-sfc component used by sfc component
      (optimized || patchFlag > 0) && !(patchFlag & 16)
    ) {
      if (patchFlag & 8) {
        const propsToUpdate = instance.vnode.dynamicProps;
        for (let i = 0; i < propsToUpdate.length; i++) {
          let key = propsToUpdate[i];
          if (isEmitListener(instance.emitsOptions, key)) {
            continue;
          }
          const value = rawProps[key];
          if (options) {
            if (hasOwn$1(attrs, key)) {
              if (value !== attrs[key]) {
                attrs[key] = value;
                hasAttrsChanged = true;
              }
            } else {
              const camelizedKey = camelize$1(key);
              props[camelizedKey] = resolvePropValue(
                options,
                rawCurrentProps,
                camelizedKey,
                value,
                instance,
                false
              );
            }
          } else {
            if (value !== attrs[key]) {
              attrs[key] = value;
              hasAttrsChanged = true;
            }
          }
        }
      }
    } else {
      if (setFullProps(instance, rawProps, props, attrs)) {
        hasAttrsChanged = true;
      }
      let kebabKey;
      for (const key in rawCurrentProps) {
        if (!rawProps || // for camelCase
        !hasOwn$1(rawProps, key) && // it's possible the original props was passed in as kebab-case
        // and converted to camelCase (#955)
        ((kebabKey = hyphenate$1(key)) === key || !hasOwn$1(rawProps, kebabKey))) {
          if (options) {
            if (rawPrevProps && // for camelCase
            (rawPrevProps[key] !== void 0 || // for kebab-case
            rawPrevProps[kebabKey] !== void 0)) {
              props[key] = resolvePropValue(
                options,
                rawCurrentProps,
                key,
                void 0,
                instance,
                true
              );
            }
          } else {
            delete props[key];
          }
        }
      }
      if (attrs !== rawCurrentProps) {
        for (const key in attrs) {
          if (!rawProps || !hasOwn$1(rawProps, key) && true) {
            delete attrs[key];
            hasAttrsChanged = true;
          }
        }
      }
    }
    if (hasAttrsChanged) {
      trigger(instance.attrs, "set", "");
    }
  }
  function setFullProps(instance, rawProps, props, attrs) {
    const [options, needCastKeys] = instance.propsOptions;
    let hasAttrsChanged = false;
    let rawCastValues;
    if (rawProps) {
      for (let key in rawProps) {
        if (isReservedProp(key)) {
          continue;
        }
        const value = rawProps[key];
        let camelKey;
        if (options && hasOwn$1(options, camelKey = camelize$1(key))) {
          if (!needCastKeys || !needCastKeys.includes(camelKey)) {
            props[camelKey] = value;
          } else {
            (rawCastValues || (rawCastValues = {}))[camelKey] = value;
          }
        } else if (!isEmitListener(instance.emitsOptions, key)) {
          if (!(key in attrs) || value !== attrs[key]) {
            attrs[key] = value;
            hasAttrsChanged = true;
          }
        }
      }
    }
    if (needCastKeys) {
      const rawCurrentProps = toRaw(props);
      const castValues = rawCastValues || EMPTY_OBJ;
      for (let i = 0; i < needCastKeys.length; i++) {
        const key = needCastKeys[i];
        props[key] = resolvePropValue(
          options,
          rawCurrentProps,
          key,
          castValues[key],
          instance,
          !hasOwn$1(castValues, key)
        );
      }
    }
    return hasAttrsChanged;
  }
  function resolvePropValue(options, props, key, value, instance, isAbsent) {
    const opt = options[key];
    if (opt != null) {
      const hasDefault = hasOwn$1(opt, "default");
      if (hasDefault && value === void 0) {
        const defaultValue = opt.default;
        if (opt.type !== Function && !opt.skipFactory && isFunction$1(defaultValue)) {
          const { propsDefaults } = instance;
          if (key in propsDefaults) {
            value = propsDefaults[key];
          } else {
            const reset = setCurrentInstance(instance);
            value = propsDefaults[key] = defaultValue.call(
              null,
              props
            );
            reset();
          }
        } else {
          value = defaultValue;
        }
        if (instance.ce) {
          instance.ce._setProp(key, value);
        }
      }
      if (opt[
        0
        /* shouldCast */
      ]) {
        if (isAbsent && !hasDefault) {
          value = false;
        } else if (opt[
          1
          /* shouldCastTrue */
        ] && (value === "" || value === hyphenate$1(key))) {
          value = true;
        }
      }
    }
    return value;
  }
  const mixinPropsCache = /* @__PURE__ */ new WeakMap();
  function normalizePropsOptions(comp, appContext, asMixin = false) {
    const cache = asMixin ? mixinPropsCache : appContext.propsCache;
    const cached = cache.get(comp);
    if (cached) {
      return cached;
    }
    const raw = comp.props;
    const normalized = {};
    const needCastKeys = [];
    let hasExtends = false;
    if (!isFunction$1(comp)) {
      const extendProps = (raw2) => {
        hasExtends = true;
        const [props, keys] = normalizePropsOptions(raw2, appContext, true);
        extend$1(normalized, props);
        if (keys)
          needCastKeys.push(...keys);
      };
      if (!asMixin && appContext.mixins.length) {
        appContext.mixins.forEach(extendProps);
      }
      if (comp.extends) {
        extendProps(comp.extends);
      }
      if (comp.mixins) {
        comp.mixins.forEach(extendProps);
      }
    }
    if (!raw && !hasExtends) {
      if (isObject(comp)) {
        cache.set(comp, EMPTY_ARR);
      }
      return EMPTY_ARR;
    }
    if (isArray$1(raw)) {
      for (let i = 0; i < raw.length; i++) {
        const normalizedKey = camelize$1(raw[i]);
        if (validatePropName(normalizedKey)) {
          normalized[normalizedKey] = EMPTY_OBJ;
        }
      }
    } else if (raw) {
      for (const key in raw) {
        const normalizedKey = camelize$1(key);
        if (validatePropName(normalizedKey)) {
          const opt = raw[key];
          const prop = normalized[normalizedKey] = isArray$1(opt) || isFunction$1(opt) ? { type: opt } : extend$1({}, opt);
          const propType = prop.type;
          let shouldCast = false;
          let shouldCastTrue = true;
          if (isArray$1(propType)) {
            for (let index = 0; index < propType.length; ++index) {
              const type = propType[index];
              const typeName = isFunction$1(type) && type.name;
              if (typeName === "Boolean") {
                shouldCast = true;
                break;
              } else if (typeName === "String") {
                shouldCastTrue = false;
              }
            }
          } else {
            shouldCast = isFunction$1(propType) && propType.name === "Boolean";
          }
          prop[
            0
            /* shouldCast */
          ] = shouldCast;
          prop[
            1
            /* shouldCastTrue */
          ] = shouldCastTrue;
          if (shouldCast || hasOwn$1(prop, "default")) {
            needCastKeys.push(normalizedKey);
          }
        }
      }
    }
    const res = [normalized, needCastKeys];
    if (isObject(comp)) {
      cache.set(comp, res);
    }
    return res;
  }
  function validatePropName(key) {
    if (key[0] !== "$" && !isReservedProp(key)) {
      return true;
    }
    return false;
  }
  const isInternalKey = (key) => key === "_" || key === "_ctx" || key === "$stable";
  const normalizeSlotValue = (value) => isArray$1(value) ? value.map(normalizeVNode) : [normalizeVNode(value)];
  const normalizeSlot = (key, rawSlot, ctx) => {
    if (rawSlot._n) {
      return rawSlot;
    }
    const normalized = withCtx((...args) => {
      if (false)
        ;
      return normalizeSlotValue(rawSlot(...args));
    }, ctx);
    normalized._c = false;
    return normalized;
  };
  const normalizeObjectSlots = (rawSlots, slots, instance) => {
    const ctx = rawSlots._ctx;
    for (const key in rawSlots) {
      if (isInternalKey(key))
        continue;
      const value = rawSlots[key];
      if (isFunction$1(value)) {
        slots[key] = normalizeSlot(key, value, ctx);
      } else if (value != null) {
        const normalized = normalizeSlotValue(value);
        slots[key] = () => normalized;
      }
    }
  };
  const normalizeVNodeSlots = (instance, children) => {
    const normalized = normalizeSlotValue(children);
    instance.slots.default = () => normalized;
  };
  const assignSlots = (slots, children, optimized) => {
    for (const key in children) {
      if (optimized || !isInternalKey(key)) {
        slots[key] = children[key];
      }
    }
  };
  const initSlots = (instance, children, optimized) => {
    const slots = instance.slots = createInternalObject();
    if (instance.vnode.shapeFlag & 32) {
      const type = children._;
      if (type) {
        assignSlots(slots, children, optimized);
        if (optimized) {
          def(slots, "_", type, true);
        }
      } else {
        normalizeObjectSlots(children, slots);
      }
    } else if (children) {
      normalizeVNodeSlots(instance, children);
    }
  };
  const updateSlots = (instance, children, optimized) => {
    const { vnode, slots } = instance;
    let needDeletionCheck = true;
    let deletionComparisonTarget = EMPTY_OBJ;
    if (vnode.shapeFlag & 32) {
      const type = children._;
      if (type) {
        if (optimized && type === 1) {
          needDeletionCheck = false;
        } else {
          assignSlots(slots, children, optimized);
        }
      } else {
        needDeletionCheck = !children.$stable;
        normalizeObjectSlots(children, slots);
      }
      deletionComparisonTarget = children;
    } else if (children) {
      normalizeVNodeSlots(instance, children);
      deletionComparisonTarget = { default: 1 };
    }
    if (needDeletionCheck) {
      for (const key in slots) {
        if (!isInternalKey(key) && deletionComparisonTarget[key] == null) {
          delete slots[key];
        }
      }
    }
  };
  const queuePostRenderEffect = queueEffectWithSuspense;
  function createRenderer(options) {
    return baseCreateRenderer(options);
  }
  function baseCreateRenderer(options, createHydrationFns) {
    const target = getGlobalThis();
    target.__VUE__ = true;
    const {
      insert: hostInsert,
      remove: hostRemove,
      patchProp: hostPatchProp,
      createElement: hostCreateElement,
      createText: hostCreateText,
      createComment: hostCreateComment,
      setText: hostSetText,
      setElementText: hostSetElementText,
      parentNode: hostParentNode,
      nextSibling: hostNextSibling,
      setScopeId: hostSetScopeId = NOOP,
      insertStaticContent: hostInsertStaticContent
    } = options;
    const patch = (n1, n2, container, anchor = null, parentComponent = null, parentSuspense = null, namespace = void 0, slotScopeIds = null, optimized = !!n2.dynamicChildren) => {
      if (n1 === n2) {
        return;
      }
      if (n1 && !isSameVNodeType(n1, n2)) {
        anchor = getNextHostNode(n1);
        unmount(n1, parentComponent, parentSuspense, true);
        n1 = null;
      }
      if (n2.patchFlag === -2) {
        optimized = false;
        n2.dynamicChildren = null;
      }
      const { type, ref: ref2, shapeFlag } = n2;
      switch (type) {
        case Text:
          processText(n1, n2, container, anchor);
          break;
        case Comment:
          processCommentNode(n1, n2, container, anchor);
          break;
        case Static:
          if (n1 == null) {
            mountStaticNode(n2, container, anchor, namespace);
          }
          break;
        case Fragment:
          processFragment(
            n1,
            n2,
            container,
            anchor,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
          break;
        default:
          if (shapeFlag & 1) {
            processElement(
              n1,
              n2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
          } else if (shapeFlag & 6) {
            processComponent(
              n1,
              n2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
          } else if (shapeFlag & 64) {
            type.process(
              n1,
              n2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized,
              internals
            );
          } else if (shapeFlag & 128) {
            type.process(
              n1,
              n2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized,
              internals
            );
          } else
            ;
      }
      if (ref2 != null && parentComponent) {
        setRef(ref2, n1 && n1.ref, parentSuspense, n2 || n1, !n2);
      } else if (ref2 == null && n1 && n1.ref != null) {
        setRef(n1.ref, null, parentSuspense, n1, true);
      }
    };
    const processText = (n1, n2, container, anchor) => {
      if (n1 == null) {
        hostInsert(
          n2.el = hostCreateText(n2.children),
          container,
          anchor
        );
      } else {
        const el = n2.el = n1.el;
        if (n2.children !== n1.children) {
          hostSetText(el, n2.children);
        }
      }
    };
    const processCommentNode = (n1, n2, container, anchor) => {
      if (n1 == null) {
        hostInsert(
          n2.el = hostCreateComment(n2.children || ""),
          container,
          anchor
        );
      } else {
        n2.el = n1.el;
      }
    };
    const mountStaticNode = (n2, container, anchor, namespace) => {
      [n2.el, n2.anchor] = hostInsertStaticContent(
        n2.children,
        container,
        anchor,
        namespace,
        n2.el,
        n2.anchor
      );
    };
    const moveStaticNode = ({ el, anchor }, container, nextSibling) => {
      let next;
      while (el && el !== anchor) {
        next = hostNextSibling(el);
        hostInsert(el, container, nextSibling);
        el = next;
      }
      hostInsert(anchor, container, nextSibling);
    };
    const removeStaticNode = ({ el, anchor }) => {
      let next;
      while (el && el !== anchor) {
        next = hostNextSibling(el);
        hostRemove(el);
        el = next;
      }
      hostRemove(anchor);
    };
    const processElement = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      if (n2.type === "svg") {
        namespace = "svg";
      } else if (n2.type === "math") {
        namespace = "mathml";
      }
      if (n1 == null) {
        mountElement(
          n2,
          container,
          anchor,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized
        );
      } else {
        patchElement(
          n1,
          n2,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized
        );
      }
    };
    const mountElement = (vnode, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      let el;
      let vnodeHook;
      const { props, shapeFlag, transition, dirs } = vnode;
      el = vnode.el = hostCreateElement(
        vnode.type,
        namespace,
        props && props.is,
        props
      );
      if (shapeFlag & 8) {
        hostSetElementText(el, vnode.children);
      } else if (shapeFlag & 16) {
        mountChildren(
          vnode.children,
          el,
          null,
          parentComponent,
          parentSuspense,
          resolveChildrenNamespace(vnode, namespace),
          slotScopeIds,
          optimized
        );
      }
      if (dirs) {
        invokeDirectiveHook(vnode, null, parentComponent, "created");
      }
      setScopeId(el, vnode, vnode.scopeId, slotScopeIds, parentComponent);
      if (props) {
        for (const key in props) {
          if (key !== "value" && !isReservedProp(key)) {
            hostPatchProp(el, key, null, props[key], namespace, parentComponent);
          }
        }
        if ("value" in props) {
          hostPatchProp(el, "value", null, props.value, namespace);
        }
        if (vnodeHook = props.onVnodeBeforeMount) {
          invokeVNodeHook(vnodeHook, parentComponent, vnode);
        }
      }
      if (dirs) {
        invokeDirectiveHook(vnode, null, parentComponent, "beforeMount");
      }
      const needCallTransitionHooks = needTransition(parentSuspense, transition);
      if (needCallTransitionHooks) {
        transition.beforeEnter(el);
      }
      hostInsert(el, container, anchor);
      if ((vnodeHook = props && props.onVnodeMounted) || needCallTransitionHooks || dirs) {
        queuePostRenderEffect(() => {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
          needCallTransitionHooks && transition.enter(el);
          dirs && invokeDirectiveHook(vnode, null, parentComponent, "mounted");
        }, parentSuspense);
      }
    };
    const setScopeId = (el, vnode, scopeId, slotScopeIds, parentComponent) => {
      if (scopeId) {
        hostSetScopeId(el, scopeId);
      }
      if (slotScopeIds) {
        for (let i = 0; i < slotScopeIds.length; i++) {
          hostSetScopeId(el, slotScopeIds[i]);
        }
      }
      if (parentComponent) {
        let subTree = parentComponent.subTree;
        if (vnode === subTree || isSuspense(subTree.type) && (subTree.ssContent === vnode || subTree.ssFallback === vnode)) {
          const parentVNode = parentComponent.vnode;
          setScopeId(
            el,
            parentVNode,
            parentVNode.scopeId,
            parentVNode.slotScopeIds,
            parentComponent.parent
          );
        }
      }
    };
    const mountChildren = (children, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized, start = 0) => {
      for (let i = start; i < children.length; i++) {
        const child = children[i] = optimized ? cloneIfMounted(children[i]) : normalizeVNode(children[i]);
        patch(
          null,
          child,
          container,
          anchor,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized
        );
      }
    };
    const patchElement = (n1, n2, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      const el = n2.el = n1.el;
      let { patchFlag, dynamicChildren, dirs } = n2;
      patchFlag |= n1.patchFlag & 16;
      const oldProps = n1.props || EMPTY_OBJ;
      const newProps = n2.props || EMPTY_OBJ;
      let vnodeHook;
      parentComponent && toggleRecurse(parentComponent, false);
      if (vnodeHook = newProps.onVnodeBeforeUpdate) {
        invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
      }
      if (dirs) {
        invokeDirectiveHook(n2, n1, parentComponent, "beforeUpdate");
      }
      parentComponent && toggleRecurse(parentComponent, true);
      if (oldProps.innerHTML && newProps.innerHTML == null || oldProps.textContent && newProps.textContent == null) {
        hostSetElementText(el, "");
      }
      if (dynamicChildren) {
        patchBlockChildren(
          n1.dynamicChildren,
          dynamicChildren,
          el,
          parentComponent,
          parentSuspense,
          resolveChildrenNamespace(n2, namespace),
          slotScopeIds
        );
      } else if (!optimized) {
        patchChildren(
          n1,
          n2,
          el,
          null,
          parentComponent,
          parentSuspense,
          resolveChildrenNamespace(n2, namespace),
          slotScopeIds,
          false
        );
      }
      if (patchFlag > 0) {
        if (patchFlag & 16) {
          patchProps(el, oldProps, newProps, parentComponent, namespace);
        } else {
          if (patchFlag & 2) {
            if (oldProps.class !== newProps.class) {
              hostPatchProp(el, "class", null, newProps.class, namespace);
            }
          }
          if (patchFlag & 4) {
            hostPatchProp(el, "style", oldProps.style, newProps.style, namespace);
          }
          if (patchFlag & 8) {
            const propsToUpdate = n2.dynamicProps;
            for (let i = 0; i < propsToUpdate.length; i++) {
              const key = propsToUpdate[i];
              const prev = oldProps[key];
              const next = newProps[key];
              if (next !== prev || key === "value") {
                hostPatchProp(el, key, prev, next, namespace, parentComponent);
              }
            }
          }
        }
        if (patchFlag & 1) {
          if (n1.children !== n2.children) {
            hostSetElementText(el, n2.children);
          }
        }
      } else if (!optimized && dynamicChildren == null) {
        patchProps(el, oldProps, newProps, parentComponent, namespace);
      }
      if ((vnodeHook = newProps.onVnodeUpdated) || dirs) {
        queuePostRenderEffect(() => {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
          dirs && invokeDirectiveHook(n2, n1, parentComponent, "updated");
        }, parentSuspense);
      }
    };
    const patchBlockChildren = (oldChildren, newChildren, fallbackContainer, parentComponent, parentSuspense, namespace, slotScopeIds) => {
      for (let i = 0; i < newChildren.length; i++) {
        const oldVNode = oldChildren[i];
        const newVNode = newChildren[i];
        const container = (
          // oldVNode may be an errored async setup() component inside Suspense
          // which will not have a mounted element
          oldVNode.el && // - In the case of a Fragment, we need to provide the actual parent
          // of the Fragment itself so it can move its children.
          (oldVNode.type === Fragment || // - In the case of different nodes, there is going to be a replacement
          // which also requires the correct parent container
          !isSameVNodeType(oldVNode, newVNode) || // - In the case of a component, it could contain anything.
          oldVNode.shapeFlag & (6 | 64 | 128)) ? hostParentNode(oldVNode.el) : (
            // In other cases, the parent container is not actually used so we
            // just pass the block element here to avoid a DOM parentNode call.
            fallbackContainer
          )
        );
        patch(
          oldVNode,
          newVNode,
          container,
          null,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          true
        );
      }
    };
    const patchProps = (el, oldProps, newProps, parentComponent, namespace) => {
      if (oldProps !== newProps) {
        if (oldProps !== EMPTY_OBJ) {
          for (const key in oldProps) {
            if (!isReservedProp(key) && !(key in newProps)) {
              hostPatchProp(
                el,
                key,
                oldProps[key],
                null,
                namespace,
                parentComponent
              );
            }
          }
        }
        for (const key in newProps) {
          if (isReservedProp(key))
            continue;
          const next = newProps[key];
          const prev = oldProps[key];
          if (next !== prev && key !== "value") {
            hostPatchProp(el, key, prev, next, namespace, parentComponent);
          }
        }
        if ("value" in newProps) {
          hostPatchProp(el, "value", oldProps.value, newProps.value, namespace);
        }
      }
    };
    const processFragment = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      const fragmentStartAnchor = n2.el = n1 ? n1.el : hostCreateText("");
      const fragmentEndAnchor = n2.anchor = n1 ? n1.anchor : hostCreateText("");
      let { patchFlag, dynamicChildren, slotScopeIds: fragmentSlotScopeIds } = n2;
      if (fragmentSlotScopeIds) {
        slotScopeIds = slotScopeIds ? slotScopeIds.concat(fragmentSlotScopeIds) : fragmentSlotScopeIds;
      }
      if (n1 == null) {
        hostInsert(fragmentStartAnchor, container, anchor);
        hostInsert(fragmentEndAnchor, container, anchor);
        mountChildren(
          // #10007
          // such fragment like `<></>` will be compiled into
          // a fragment which doesn't have a children.
          // In this case fallback to an empty array
          n2.children || [],
          container,
          fragmentEndAnchor,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized
        );
      } else {
        if (patchFlag > 0 && patchFlag & 64 && dynamicChildren && // #2715 the previous fragment could've been a BAILed one as a result
        // of renderSlot() with no valid children
        n1.dynamicChildren) {
          patchBlockChildren(
            n1.dynamicChildren,
            dynamicChildren,
            container,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds
          );
          if (
            // #2080 if the stable fragment has a key, it's a <template v-for> that may
            //  get moved around. Make sure all root level vnodes inherit el.
            // #2134 or if it's a component root, it may also get moved around
            // as the component is being moved.
            n2.key != null || parentComponent && n2 === parentComponent.subTree
          ) {
            traverseStaticChildren(
              n1,
              n2,
              true
              /* shallow */
            );
          }
        } else {
          patchChildren(
            n1,
            n2,
            container,
            fragmentEndAnchor,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
        }
      }
    };
    const processComponent = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      n2.slotScopeIds = slotScopeIds;
      if (n1 == null) {
        if (n2.shapeFlag & 512) {
          parentComponent.ctx.activate(
            n2,
            container,
            anchor,
            namespace,
            optimized
          );
        } else {
          mountComponent(
            n2,
            container,
            anchor,
            parentComponent,
            parentSuspense,
            namespace,
            optimized
          );
        }
      } else {
        updateComponent(n1, n2, optimized);
      }
    };
    const mountComponent = (initialVNode, container, anchor, parentComponent, parentSuspense, namespace, optimized) => {
      const instance = initialVNode.component = createComponentInstance(
        initialVNode,
        parentComponent,
        parentSuspense
      );
      if (isKeepAlive(initialVNode)) {
        instance.ctx.renderer = internals;
      }
      {
        setupComponent(instance, false, optimized);
      }
      if (instance.asyncDep) {
        parentSuspense && parentSuspense.registerDep(instance, setupRenderEffect, optimized);
        if (!initialVNode.el) {
          const placeholder = instance.subTree = createVNode(Comment);
          processCommentNode(null, placeholder, container, anchor);
          initialVNode.placeholder = placeholder.el;
        }
      } else {
        setupRenderEffect(
          instance,
          initialVNode,
          container,
          anchor,
          parentSuspense,
          namespace,
          optimized
        );
      }
    };
    const updateComponent = (n1, n2, optimized) => {
      const instance = n2.component = n1.component;
      if (shouldUpdateComponent(n1, n2, optimized)) {
        if (instance.asyncDep && !instance.asyncResolved) {
          updateComponentPreRender(instance, n2, optimized);
          return;
        } else {
          instance.next = n2;
          instance.update();
        }
      } else {
        n2.el = n1.el;
        instance.vnode = n2;
      }
    };
    const setupRenderEffect = (instance, initialVNode, container, anchor, parentSuspense, namespace, optimized) => {
      const componentUpdateFn = () => {
        if (!instance.isMounted) {
          let vnodeHook;
          const { el, props } = initialVNode;
          const { bm, m, parent, root, type } = instance;
          const isAsyncWrapperVNode = isAsyncWrapper(initialVNode);
          toggleRecurse(instance, false);
          if (bm) {
            invokeArrayFns(bm);
          }
          if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeBeforeMount)) {
            invokeVNodeHook(vnodeHook, parent, initialVNode);
          }
          toggleRecurse(instance, true);
          if (el && hydrateNode) {
            const hydrateSubTree = () => {
              instance.subTree = renderComponentRoot(instance);
              hydrateNode(
                el,
                instance.subTree,
                instance,
                parentSuspense,
                null
              );
            };
            if (isAsyncWrapperVNode && type.__asyncHydrate) {
              type.__asyncHydrate(
                el,
                instance,
                hydrateSubTree
              );
            } else {
              hydrateSubTree();
            }
          } else {
            if (root.ce && // @ts-expect-error _def is private
            root.ce._def.shadowRoot !== false) {
              root.ce._injectChildStyle(type);
            }
            const subTree = instance.subTree = renderComponentRoot(instance);
            patch(
              null,
              subTree,
              container,
              anchor,
              instance,
              parentSuspense,
              namespace
            );
            initialVNode.el = subTree.el;
          }
          if (m) {
            queuePostRenderEffect(m, parentSuspense);
          }
          if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeMounted)) {
            const scopedInitialVNode = initialVNode;
            queuePostRenderEffect(
              () => invokeVNodeHook(vnodeHook, parent, scopedInitialVNode),
              parentSuspense
            );
          }
          if (initialVNode.shapeFlag & 256 || parent && isAsyncWrapper(parent.vnode) && parent.vnode.shapeFlag & 256) {
            instance.a && queuePostRenderEffect(instance.a, parentSuspense);
          }
          instance.isMounted = true;
          initialVNode = container = anchor = null;
        } else {
          let { next, bu, u, parent, vnode } = instance;
          {
            const nonHydratedAsyncRoot = locateNonHydratedAsyncRoot(instance);
            if (nonHydratedAsyncRoot) {
              if (next) {
                next.el = vnode.el;
                updateComponentPreRender(instance, next, optimized);
              }
              nonHydratedAsyncRoot.asyncDep.then(() => {
                if (!instance.isUnmounted) {
                  componentUpdateFn();
                }
              });
              return;
            }
          }
          let originNext = next;
          let vnodeHook;
          toggleRecurse(instance, false);
          if (next) {
            next.el = vnode.el;
            updateComponentPreRender(instance, next, optimized);
          } else {
            next = vnode;
          }
          if (bu) {
            invokeArrayFns(bu);
          }
          if (vnodeHook = next.props && next.props.onVnodeBeforeUpdate) {
            invokeVNodeHook(vnodeHook, parent, next, vnode);
          }
          toggleRecurse(instance, true);
          const nextTree = renderComponentRoot(instance);
          const prevTree = instance.subTree;
          instance.subTree = nextTree;
          patch(
            prevTree,
            nextTree,
            // parent may have changed if it's in a teleport
            hostParentNode(prevTree.el),
            // anchor may have changed if it's in a fragment
            getNextHostNode(prevTree),
            instance,
            parentSuspense,
            namespace
          );
          next.el = nextTree.el;
          if (originNext === null) {
            updateHOCHostEl(instance, nextTree.el);
          }
          if (u) {
            queuePostRenderEffect(u, parentSuspense);
          }
          if (vnodeHook = next.props && next.props.onVnodeUpdated) {
            queuePostRenderEffect(
              () => invokeVNodeHook(vnodeHook, parent, next, vnode),
              parentSuspense
            );
          }
        }
      };
      instance.scope.on();
      const effect = instance.effect = new ReactiveEffect(componentUpdateFn);
      instance.scope.off();
      const update = instance.update = effect.run.bind(effect);
      const job = instance.job = effect.runIfDirty.bind(effect);
      job.i = instance;
      job.id = instance.uid;
      effect.scheduler = () => queueJob(job);
      toggleRecurse(instance, true);
      update();
    };
    const updateComponentPreRender = (instance, nextVNode, optimized) => {
      nextVNode.component = instance;
      const prevProps = instance.vnode.props;
      instance.vnode = nextVNode;
      instance.next = null;
      updateProps(instance, nextVNode.props, prevProps, optimized);
      updateSlots(instance, nextVNode.children, optimized);
      pauseTracking();
      flushPreFlushCbs(instance);
      resetTracking();
    };
    const patchChildren = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized = false) => {
      const c1 = n1 && n1.children;
      const prevShapeFlag = n1 ? n1.shapeFlag : 0;
      const c2 = n2.children;
      const { patchFlag, shapeFlag } = n2;
      if (patchFlag > 0) {
        if (patchFlag & 128) {
          patchKeyedChildren(
            c1,
            c2,
            container,
            anchor,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
          return;
        } else if (patchFlag & 256) {
          patchUnkeyedChildren(
            c1,
            c2,
            container,
            anchor,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
          return;
        }
      }
      if (shapeFlag & 8) {
        if (prevShapeFlag & 16) {
          unmountChildren(c1, parentComponent, parentSuspense);
        }
        if (c2 !== c1) {
          hostSetElementText(container, c2);
        }
      } else {
        if (prevShapeFlag & 16) {
          if (shapeFlag & 16) {
            patchKeyedChildren(
              c1,
              c2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
          } else {
            unmountChildren(c1, parentComponent, parentSuspense, true);
          }
        } else {
          if (prevShapeFlag & 8) {
            hostSetElementText(container, "");
          }
          if (shapeFlag & 16) {
            mountChildren(
              c2,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
          }
        }
      }
    };
    const patchUnkeyedChildren = (c1, c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      c1 = c1 || EMPTY_ARR;
      c2 = c2 || EMPTY_ARR;
      const oldLength = c1.length;
      const newLength = c2.length;
      const commonLength = Math.min(oldLength, newLength);
      let i;
      for (i = 0; i < commonLength; i++) {
        const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
        patch(
          c1[i],
          nextChild,
          container,
          null,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized
        );
      }
      if (oldLength > newLength) {
        unmountChildren(
          c1,
          parentComponent,
          parentSuspense,
          true,
          false,
          commonLength
        );
      } else {
        mountChildren(
          c2,
          container,
          anchor,
          parentComponent,
          parentSuspense,
          namespace,
          slotScopeIds,
          optimized,
          commonLength
        );
      }
    };
    const patchKeyedChildren = (c1, c2, container, parentAnchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
      let i = 0;
      const l2 = c2.length;
      let e1 = c1.length - 1;
      let e2 = l2 - 1;
      while (i <= e1 && i <= e2) {
        const n1 = c1[i];
        const n2 = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
        if (isSameVNodeType(n1, n2)) {
          patch(
            n1,
            n2,
            container,
            null,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
        } else {
          break;
        }
        i++;
      }
      while (i <= e1 && i <= e2) {
        const n1 = c1[e1];
        const n2 = c2[e2] = optimized ? cloneIfMounted(c2[e2]) : normalizeVNode(c2[e2]);
        if (isSameVNodeType(n1, n2)) {
          patch(
            n1,
            n2,
            container,
            null,
            parentComponent,
            parentSuspense,
            namespace,
            slotScopeIds,
            optimized
          );
        } else {
          break;
        }
        e1--;
        e2--;
      }
      if (i > e1) {
        if (i <= e2) {
          const nextPos = e2 + 1;
          const anchor = nextPos < l2 ? c2[nextPos].el : parentAnchor;
          while (i <= e2) {
            patch(
              null,
              c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]),
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
            i++;
          }
        }
      } else if (i > e2) {
        while (i <= e1) {
          unmount(c1[i], parentComponent, parentSuspense, true);
          i++;
        }
      } else {
        const s1 = i;
        const s2 = i;
        const keyToNewIndexMap = /* @__PURE__ */ new Map();
        for (i = s2; i <= e2; i++) {
          const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
          if (nextChild.key != null) {
            keyToNewIndexMap.set(nextChild.key, i);
          }
        }
        let j;
        let patched = 0;
        const toBePatched = e2 - s2 + 1;
        let moved = false;
        let maxNewIndexSoFar = 0;
        const newIndexToOldIndexMap = new Array(toBePatched);
        for (i = 0; i < toBePatched; i++)
          newIndexToOldIndexMap[i] = 0;
        for (i = s1; i <= e1; i++) {
          const prevChild = c1[i];
          if (patched >= toBePatched) {
            unmount(prevChild, parentComponent, parentSuspense, true);
            continue;
          }
          let newIndex;
          if (prevChild.key != null) {
            newIndex = keyToNewIndexMap.get(prevChild.key);
          } else {
            for (j = s2; j <= e2; j++) {
              if (newIndexToOldIndexMap[j - s2] === 0 && isSameVNodeType(prevChild, c2[j])) {
                newIndex = j;
                break;
              }
            }
          }
          if (newIndex === void 0) {
            unmount(prevChild, parentComponent, parentSuspense, true);
          } else {
            newIndexToOldIndexMap[newIndex - s2] = i + 1;
            if (newIndex >= maxNewIndexSoFar) {
              maxNewIndexSoFar = newIndex;
            } else {
              moved = true;
            }
            patch(
              prevChild,
              c2[newIndex],
              container,
              null,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
            patched++;
          }
        }
        const increasingNewIndexSequence = moved ? getSequence(newIndexToOldIndexMap) : EMPTY_ARR;
        j = increasingNewIndexSequence.length - 1;
        for (i = toBePatched - 1; i >= 0; i--) {
          const nextIndex = s2 + i;
          const nextChild = c2[nextIndex];
          const anchorVNode = c2[nextIndex + 1];
          const anchor = nextIndex + 1 < l2 ? (
            // #13559, fallback to el placeholder for unresolved async component
            anchorVNode.el || anchorVNode.placeholder
          ) : parentAnchor;
          if (newIndexToOldIndexMap[i] === 0) {
            patch(
              null,
              nextChild,
              container,
              anchor,
              parentComponent,
              parentSuspense,
              namespace,
              slotScopeIds,
              optimized
            );
          } else if (moved) {
            if (j < 0 || i !== increasingNewIndexSequence[j]) {
              move(nextChild, container, anchor, 2);
            } else {
              j--;
            }
          }
        }
      }
    };
    const move = (vnode, container, anchor, moveType, parentSuspense = null) => {
      const { el, type, transition, children, shapeFlag } = vnode;
      if (shapeFlag & 6) {
        move(vnode.component.subTree, container, anchor, moveType);
        return;
      }
      if (shapeFlag & 128) {
        vnode.suspense.move(container, anchor, moveType);
        return;
      }
      if (shapeFlag & 64) {
        type.move(vnode, container, anchor, internals);
        return;
      }
      if (type === Fragment) {
        hostInsert(el, container, anchor);
        for (let i = 0; i < children.length; i++) {
          move(children[i], container, anchor, moveType);
        }
        hostInsert(vnode.anchor, container, anchor);
        return;
      }
      if (type === Static) {
        moveStaticNode(vnode, container, anchor);
        return;
      }
      const needTransition2 = moveType !== 2 && shapeFlag & 1 && transition;
      if (needTransition2) {
        if (moveType === 0) {
          transition.beforeEnter(el);
          hostInsert(el, container, anchor);
          queuePostRenderEffect(() => transition.enter(el), parentSuspense);
        } else {
          const { leave, delayLeave, afterLeave } = transition;
          const remove22 = () => {
            if (vnode.ctx.isUnmounted) {
              hostRemove(el);
            } else {
              hostInsert(el, container, anchor);
            }
          };
          const performLeave = () => {
            if (el._isLeaving) {
              el[leaveCbKey](
                true
                /* cancelled */
              );
            }
            leave(el, () => {
              remove22();
              afterLeave && afterLeave();
            });
          };
          if (delayLeave) {
            delayLeave(el, remove22, performLeave);
          } else {
            performLeave();
          }
        }
      } else {
        hostInsert(el, container, anchor);
      }
    };
    const unmount = (vnode, parentComponent, parentSuspense, doRemove = false, optimized = false) => {
      const {
        type,
        props,
        ref: ref2,
        children,
        dynamicChildren,
        shapeFlag,
        patchFlag,
        dirs,
        cacheIndex
      } = vnode;
      if (patchFlag === -2) {
        optimized = false;
      }
      if (ref2 != null) {
        pauseTracking();
        setRef(ref2, null, parentSuspense, vnode, true);
        resetTracking();
      }
      if (cacheIndex != null) {
        parentComponent.renderCache[cacheIndex] = void 0;
      }
      if (shapeFlag & 256) {
        parentComponent.ctx.deactivate(vnode);
        return;
      }
      const shouldInvokeDirs = shapeFlag & 1 && dirs;
      const shouldInvokeVnodeHook = !isAsyncWrapper(vnode);
      let vnodeHook;
      if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeBeforeUnmount)) {
        invokeVNodeHook(vnodeHook, parentComponent, vnode);
      }
      if (shapeFlag & 6) {
        unmountComponent(vnode.component, parentSuspense, doRemove);
      } else {
        if (shapeFlag & 128) {
          vnode.suspense.unmount(parentSuspense, doRemove);
          return;
        }
        if (shouldInvokeDirs) {
          invokeDirectiveHook(vnode, null, parentComponent, "beforeUnmount");
        }
        if (shapeFlag & 64) {
          vnode.type.remove(
            vnode,
            parentComponent,
            parentSuspense,
            internals,
            doRemove
          );
        } else if (dynamicChildren && // #5154
        // when v-once is used inside a block, setBlockTracking(-1) marks the
        // parent block with hasOnce: true
        // so that it doesn't take the fast path during unmount - otherwise
        // components nested in v-once are never unmounted.
        !dynamicChildren.hasOnce && // #1153: fast path should not be taken for non-stable (v-for) fragments
        (type !== Fragment || patchFlag > 0 && patchFlag & 64)) {
          unmountChildren(
            dynamicChildren,
            parentComponent,
            parentSuspense,
            false,
            true
          );
        } else if (type === Fragment && patchFlag & (128 | 256) || !optimized && shapeFlag & 16) {
          unmountChildren(children, parentComponent, parentSuspense);
        }
        if (doRemove) {
          remove2(vnode);
        }
      }
      if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeUnmounted) || shouldInvokeDirs) {
        queuePostRenderEffect(() => {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
          shouldInvokeDirs && invokeDirectiveHook(vnode, null, parentComponent, "unmounted");
        }, parentSuspense);
      }
    };
    const remove2 = (vnode) => {
      const { type, el, anchor, transition } = vnode;
      if (type === Fragment) {
        {
          removeFragment(el, anchor);
        }
        return;
      }
      if (type === Static) {
        removeStaticNode(vnode);
        return;
      }
      const performRemove = () => {
        hostRemove(el);
        if (transition && !transition.persisted && transition.afterLeave) {
          transition.afterLeave();
        }
      };
      if (vnode.shapeFlag & 1 && transition && !transition.persisted) {
        const { leave, delayLeave } = transition;
        const performLeave = () => leave(el, performRemove);
        if (delayLeave) {
          delayLeave(vnode.el, performRemove, performLeave);
        } else {
          performLeave();
        }
      } else {
        performRemove();
      }
    };
    const removeFragment = (cur, end) => {
      let next;
      while (cur !== end) {
        next = hostNextSibling(cur);
        hostRemove(cur);
        cur = next;
      }
      hostRemove(end);
    };
    const unmountComponent = (instance, parentSuspense, doRemove) => {
      const { bum, scope, job, subTree, um, m, a } = instance;
      invalidateMount(m);
      invalidateMount(a);
      if (bum) {
        invokeArrayFns(bum);
      }
      scope.stop();
      if (job) {
        job.flags |= 8;
        unmount(subTree, instance, parentSuspense, doRemove);
      }
      if (um) {
        queuePostRenderEffect(um, parentSuspense);
      }
      queuePostRenderEffect(() => {
        instance.isUnmounted = true;
      }, parentSuspense);
    };
    const unmountChildren = (children, parentComponent, parentSuspense, doRemove = false, optimized = false, start = 0) => {
      for (let i = start; i < children.length; i++) {
        unmount(children[i], parentComponent, parentSuspense, doRemove, optimized);
      }
    };
    const getNextHostNode = (vnode) => {
      if (vnode.shapeFlag & 6) {
        return getNextHostNode(vnode.component.subTree);
      }
      if (vnode.shapeFlag & 128) {
        return vnode.suspense.next();
      }
      const el = hostNextSibling(vnode.anchor || vnode.el);
      const teleportEnd = el && el[TeleportEndKey];
      return teleportEnd ? hostNextSibling(teleportEnd) : el;
    };
    let isFlushing = false;
    const render2 = (vnode, container, namespace) => {
      if (vnode == null) {
        if (container._vnode) {
          unmount(container._vnode, null, null, true);
        }
      } else {
        patch(
          container._vnode || null,
          vnode,
          container,
          null,
          null,
          null,
          namespace
        );
      }
      container._vnode = vnode;
      if (!isFlushing) {
        isFlushing = true;
        flushPreFlushCbs();
        flushPostFlushCbs();
        isFlushing = false;
      }
    };
    const internals = {
      p: patch,
      um: unmount,
      m: move,
      r: remove2,
      mt: mountComponent,
      mc: mountChildren,
      pc: patchChildren,
      pbc: patchBlockChildren,
      n: getNextHostNode,
      o: options
    };
    let hydrate;
    let hydrateNode;
    if (createHydrationFns) {
      [hydrate, hydrateNode] = createHydrationFns(
        internals
      );
    }
    return {
      render: render2,
      hydrate,
      createApp: createAppAPI(render2, hydrate)
    };
  }
  function resolveChildrenNamespace({ type, props }, currentNamespace) {
    return currentNamespace === "svg" && type === "foreignObject" || currentNamespace === "mathml" && type === "annotation-xml" && props && props.encoding && props.encoding.includes("html") ? void 0 : currentNamespace;
  }
  function toggleRecurse({ effect, job }, allowed) {
    if (allowed) {
      effect.flags |= 32;
      job.flags |= 4;
    } else {
      effect.flags &= -33;
      job.flags &= -5;
    }
  }
  function needTransition(parentSuspense, transition) {
    return (!parentSuspense || parentSuspense && !parentSuspense.pendingBranch) && transition && !transition.persisted;
  }
  function traverseStaticChildren(n1, n2, shallow = false) {
    const ch1 = n1.children;
    const ch2 = n2.children;
    if (isArray$1(ch1) && isArray$1(ch2)) {
      for (let i = 0; i < ch1.length; i++) {
        const c1 = ch1[i];
        let c2 = ch2[i];
        if (c2.shapeFlag & 1 && !c2.dynamicChildren) {
          if (c2.patchFlag <= 0 || c2.patchFlag === 32) {
            c2 = ch2[i] = cloneIfMounted(ch2[i]);
            c2.el = c1.el;
          }
          if (!shallow && c2.patchFlag !== -2)
            traverseStaticChildren(c1, c2);
        }
        if (c2.type === Text && // avoid cached text nodes retaining detached dom nodes
        c2.patchFlag !== -1) {
          c2.el = c1.el;
        }
        if (c2.type === Comment && !c2.el) {
          c2.el = c1.el;
        }
      }
    }
  }
  function getSequence(arr) {
    const p2 = arr.slice();
    const result = [0];
    let i, j, u, v, c;
    const len = arr.length;
    for (i = 0; i < len; i++) {
      const arrI = arr[i];
      if (arrI !== 0) {
        j = result[result.length - 1];
        if (arr[j] < arrI) {
          p2[i] = j;
          result.push(i);
          continue;
        }
        u = 0;
        v = result.length - 1;
        while (u < v) {
          c = u + v >> 1;
          if (arr[result[c]] < arrI) {
            u = c + 1;
          } else {
            v = c;
          }
        }
        if (arrI < arr[result[u]]) {
          if (u > 0) {
            p2[i] = result[u - 1];
          }
          result[u] = i;
        }
      }
    }
    u = result.length;
    v = result[u - 1];
    while (u-- > 0) {
      result[u] = v;
      v = p2[v];
    }
    return result;
  }
  function locateNonHydratedAsyncRoot(instance) {
    const subComponent = instance.subTree.component;
    if (subComponent) {
      if (subComponent.asyncDep && !subComponent.asyncResolved) {
        return subComponent;
      } else {
        return locateNonHydratedAsyncRoot(subComponent);
      }
    }
  }
  function invalidateMount(hooks) {
    if (hooks) {
      for (let i = 0; i < hooks.length; i++)
        hooks[i].flags |= 8;
    }
  }
  const ssrContextKey = Symbol.for("v-scx");
  const useSSRContext = () => {
    {
      const ctx = inject(ssrContextKey);
      return ctx;
    }
  };
  function watch(source, cb, options) {
    return doWatch(source, cb, options);
  }
  function doWatch(source, cb, options = EMPTY_OBJ) {
    const { immediate, deep, flush, once } = options;
    const baseWatchOptions = extend$1({}, options);
    const runsImmediately = cb && immediate || !cb && flush !== "post";
    let ssrCleanup;
    if (isInSSRComponentSetup) {
      if (flush === "sync") {
        const ctx = useSSRContext();
        ssrCleanup = ctx.__watcherHandles || (ctx.__watcherHandles = []);
      } else if (!runsImmediately) {
        const watchStopHandle = () => {
        };
        watchStopHandle.stop = NOOP;
        watchStopHandle.resume = NOOP;
        watchStopHandle.pause = NOOP;
        return watchStopHandle;
      }
    }
    const instance = currentInstance;
    baseWatchOptions.call = (fn, type, args) => callWithAsyncErrorHandling(fn, instance, type, args);
    let isPre = false;
    if (flush === "post") {
      baseWatchOptions.scheduler = (job) => {
        queuePostRenderEffect(job, instance && instance.suspense);
      };
    } else if (flush !== "sync") {
      isPre = true;
      baseWatchOptions.scheduler = (job, isFirstRun) => {
        if (isFirstRun) {
          job();
        } else {
          queueJob(job);
        }
      };
    }
    baseWatchOptions.augmentJob = (job) => {
      if (cb) {
        job.flags |= 4;
      }
      if (isPre) {
        job.flags |= 2;
        if (instance) {
          job.id = instance.uid;
          job.i = instance;
        }
      }
    };
    const watchHandle = watch$1(source, cb, baseWatchOptions);
    if (isInSSRComponentSetup) {
      if (ssrCleanup) {
        ssrCleanup.push(watchHandle);
      } else if (runsImmediately) {
        watchHandle();
      }
    }
    return watchHandle;
  }
  function instanceWatch(source, value, options) {
    const publicThis = this.proxy;
    const getter = isString$1(source) ? source.includes(".") ? createPathGetter(publicThis, source) : () => publicThis[source] : source.bind(publicThis, publicThis);
    let cb;
    if (isFunction$1(value)) {
      cb = value;
    } else {
      cb = value.handler;
      options = value;
    }
    const reset = setCurrentInstance(this);
    const res = doWatch(getter, cb.bind(publicThis), options);
    reset();
    return res;
  }
  function createPathGetter(ctx, path) {
    const segments = path.split(".");
    return () => {
      let cur = ctx;
      for (let i = 0; i < segments.length && cur; i++) {
        cur = cur[segments[i]];
      }
      return cur;
    };
  }
  const getModelModifiers = (props, modelName) => {
    return modelName === "modelValue" || modelName === "model-value" ? props.modelModifiers : props[`${modelName}Modifiers`] || props[`${camelize$1(modelName)}Modifiers`] || props[`${hyphenate$1(modelName)}Modifiers`];
  };
  function emit(instance, event, ...rawArgs) {
    if (instance.isUnmounted)
      return;
    const props = instance.vnode.props || EMPTY_OBJ;
    let args = rawArgs;
    const isModelListener2 = event.startsWith("update:");
    const modifiers = isModelListener2 && getModelModifiers(props, event.slice(7));
    if (modifiers) {
      if (modifiers.trim) {
        args = rawArgs.map((a) => isString$1(a) ? a.trim() : a);
      }
      if (modifiers.number) {
        args = rawArgs.map(looseToNumber);
      }
    }
    let handlerName;
    let handler = props[handlerName = toHandlerKey(event)] || // also try camelCase event handler (#2249)
    props[handlerName = toHandlerKey(camelize$1(event))];
    if (!handler && isModelListener2) {
      handler = props[handlerName = toHandlerKey(hyphenate$1(event))];
    }
    if (handler) {
      callWithAsyncErrorHandling(
        handler,
        instance,
        6,
        args
      );
    }
    const onceHandler = props[handlerName + `Once`];
    if (onceHandler) {
      if (!instance.emitted) {
        instance.emitted = {};
      } else if (instance.emitted[handlerName]) {
        return;
      }
      instance.emitted[handlerName] = true;
      callWithAsyncErrorHandling(
        onceHandler,
        instance,
        6,
        args
      );
    }
  }
  const mixinEmitsCache = /* @__PURE__ */ new WeakMap();
  function normalizeEmitsOptions(comp, appContext, asMixin = false) {
    const cache = asMixin ? mixinEmitsCache : appContext.emitsCache;
    const cached = cache.get(comp);
    if (cached !== void 0) {
      return cached;
    }
    const raw = comp.emits;
    let normalized = {};
    let hasExtends = false;
    if (!isFunction$1(comp)) {
      const extendEmits = (raw2) => {
        const normalizedFromExtend = normalizeEmitsOptions(raw2, appContext, true);
        if (normalizedFromExtend) {
          hasExtends = true;
          extend$1(normalized, normalizedFromExtend);
        }
      };
      if (!asMixin && appContext.mixins.length) {
        appContext.mixins.forEach(extendEmits);
      }
      if (comp.extends) {
        extendEmits(comp.extends);
      }
      if (comp.mixins) {
        comp.mixins.forEach(extendEmits);
      }
    }
    if (!raw && !hasExtends) {
      if (isObject(comp)) {
        cache.set(comp, null);
      }
      return null;
    }
    if (isArray$1(raw)) {
      raw.forEach((key) => normalized[key] = null);
    } else {
      extend$1(normalized, raw);
    }
    if (isObject(comp)) {
      cache.set(comp, normalized);
    }
    return normalized;
  }
  function isEmitListener(options, key) {
    if (!options || !isOn$1(key)) {
      return false;
    }
    key = key.slice(2).replace(/Once$/, "");
    return hasOwn$1(options, key[0].toLowerCase() + key.slice(1)) || hasOwn$1(options, hyphenate$1(key)) || hasOwn$1(options, key);
  }
  function markAttrsAccessed() {
  }
  function renderComponentRoot(instance) {
    const {
      type: Component,
      vnode,
      proxy,
      withProxy,
      propsOptions: [propsOptions],
      slots,
      attrs,
      emit: emit2,
      render: render2,
      renderCache,
      props,
      data,
      setupState,
      ctx,
      inheritAttrs
    } = instance;
    const prev = setCurrentRenderingInstance(instance);
    let result;
    let fallthroughAttrs;
    try {
      if (vnode.shapeFlag & 4) {
        const proxyToUse = withProxy || proxy;
        const thisProxy = false ? new Proxy(proxyToUse, {
          get(target, key, receiver) {
            warn$1(
              `Property '${String(
                key
              )}' was accessed via 'this'. Avoid using 'this' in templates.`
            );
            return Reflect.get(target, key, receiver);
          }
        }) : proxyToUse;
        result = normalizeVNode(
          render2.call(
            thisProxy,
            proxyToUse,
            renderCache,
            false ? shallowReadonly(props) : props,
            setupState,
            data,
            ctx
          )
        );
        fallthroughAttrs = attrs;
      } else {
        const render22 = Component;
        if (false)
          ;
        result = normalizeVNode(
          render22.length > 1 ? render22(
            false ? shallowReadonly(props) : props,
            false ? {
              get attrs() {
                markAttrsAccessed();
                return shallowReadonly(attrs);
              },
              slots,
              emit: emit2
            } : { attrs, slots, emit: emit2 }
          ) : render22(
            false ? shallowReadonly(props) : props,
            null
          )
        );
        fallthroughAttrs = Component.props ? attrs : getFunctionalFallthrough(attrs);
      }
    } catch (err) {
      blockStack.length = 0;
      handleError(err, instance, 1);
      result = createVNode(Comment);
    }
    let root = result;
    if (fallthroughAttrs && inheritAttrs !== false) {
      const keys = Object.keys(fallthroughAttrs);
      const { shapeFlag } = root;
      if (keys.length) {
        if (shapeFlag & (1 | 6)) {
          if (propsOptions && keys.some(isModelListener$1)) {
            fallthroughAttrs = filterModelListeners(
              fallthroughAttrs,
              propsOptions
            );
          }
          root = cloneVNode(root, fallthroughAttrs, false, true);
        }
      }
    }
    if (vnode.dirs) {
      root = cloneVNode(root, null, false, true);
      root.dirs = root.dirs ? root.dirs.concat(vnode.dirs) : vnode.dirs;
    }
    if (vnode.transition) {
      setTransitionHooks(root, vnode.transition);
    }
    {
      result = root;
    }
    setCurrentRenderingInstance(prev);
    return result;
  }
  const getFunctionalFallthrough = (attrs) => {
    let res;
    for (const key in attrs) {
      if (key === "class" || key === "style" || isOn$1(key)) {
        (res || (res = {}))[key] = attrs[key];
      }
    }
    return res;
  };
  const filterModelListeners = (attrs, props) => {
    const res = {};
    for (const key in attrs) {
      if (!isModelListener$1(key) || !(key.slice(9) in props)) {
        res[key] = attrs[key];
      }
    }
    return res;
  };
  function shouldUpdateComponent(prevVNode, nextVNode, optimized) {
    const { props: prevProps, children: prevChildren, component } = prevVNode;
    const { props: nextProps, children: nextChildren, patchFlag } = nextVNode;
    const emits = component.emitsOptions;
    if (nextVNode.dirs || nextVNode.transition) {
      return true;
    }
    if (optimized && patchFlag >= 0) {
      if (patchFlag & 1024) {
        return true;
      }
      if (patchFlag & 16) {
        if (!prevProps) {
          return !!nextProps;
        }
        return hasPropsChanged(prevProps, nextProps, emits);
      } else if (patchFlag & 8) {
        const dynamicProps = nextVNode.dynamicProps;
        for (let i = 0; i < dynamicProps.length; i++) {
          const key = dynamicProps[i];
          if (nextProps[key] !== prevProps[key] && !isEmitListener(emits, key)) {
            return true;
          }
        }
      }
    } else {
      if (prevChildren || nextChildren) {
        if (!nextChildren || !nextChildren.$stable) {
          return true;
        }
      }
      if (prevProps === nextProps) {
        return false;
      }
      if (!prevProps) {
        return !!nextProps;
      }
      if (!nextProps) {
        return true;
      }
      return hasPropsChanged(prevProps, nextProps, emits);
    }
    return false;
  }
  function hasPropsChanged(prevProps, nextProps, emitsOptions) {
    const nextKeys = Object.keys(nextProps);
    if (nextKeys.length !== Object.keys(prevProps).length) {
      return true;
    }
    for (let i = 0; i < nextKeys.length; i++) {
      const key = nextKeys[i];
      if (nextProps[key] !== prevProps[key] && !isEmitListener(emitsOptions, key)) {
        return true;
      }
    }
    return false;
  }
  function updateHOCHostEl({ vnode, parent }, el) {
    while (parent) {
      const root = parent.subTree;
      if (root.suspense && root.suspense.activeBranch === vnode) {
        root.el = vnode.el;
      }
      if (root === vnode) {
        (vnode = parent.vnode).el = el;
        parent = parent.parent;
      } else {
        break;
      }
    }
  }
  const isSuspense = (type) => type.__isSuspense;
  function queueEffectWithSuspense(fn, suspense) {
    if (suspense && suspense.pendingBranch) {
      if (isArray$1(fn)) {
        suspense.effects.push(...fn);
      } else {
        suspense.effects.push(fn);
      }
    } else {
      queuePostFlushCb(fn);
    }
  }
  const Fragment = Symbol.for("v-fgt");
  const Text = Symbol.for("v-txt");
  const Comment = Symbol.for("v-cmt");
  const Static = Symbol.for("v-stc");
  const blockStack = [];
  let currentBlock = null;
  function openBlock(disableTracking = false) {
    blockStack.push(currentBlock = disableTracking ? null : []);
  }
  function closeBlock() {
    blockStack.pop();
    currentBlock = blockStack[blockStack.length - 1] || null;
  }
  let isBlockTreeEnabled = 1;
  function setBlockTracking(value, inVOnce = false) {
    isBlockTreeEnabled += value;
    if (value < 0 && currentBlock && inVOnce) {
      currentBlock.hasOnce = true;
    }
  }
  function setupBlock(vnode) {
    vnode.dynamicChildren = isBlockTreeEnabled > 0 ? currentBlock || EMPTY_ARR : null;
    closeBlock();
    if (isBlockTreeEnabled > 0 && currentBlock) {
      currentBlock.push(vnode);
    }
    return vnode;
  }
  function createElementBlock(type, props, children, patchFlag, dynamicProps, shapeFlag) {
    return setupBlock(
      createBaseVNode(
        type,
        props,
        children,
        patchFlag,
        dynamicProps,
        shapeFlag,
        true
      )
    );
  }
  function createBlock(type, props, children, patchFlag, dynamicProps) {
    return setupBlock(
      createVNode(
        type,
        props,
        children,
        patchFlag,
        dynamicProps,
        true
      )
    );
  }
  function isVNode(value) {
    return value ? value.__v_isVNode === true : false;
  }
  function isSameVNodeType(n1, n2) {
    return n1.type === n2.type && n1.key === n2.key;
  }
  const normalizeKey = ({ key }) => key != null ? key : null;
  const normalizeRef = ({
    ref: ref2,
    ref_key,
    ref_for
  }) => {
    if (typeof ref2 === "number") {
      ref2 = "" + ref2;
    }
    return ref2 != null ? isString$1(ref2) || isRef(ref2) || isFunction$1(ref2) ? { i: currentRenderingInstance, r: ref2, k: ref_key, f: !!ref_for } : ref2 : null;
  };
  function createBaseVNode(type, props = null, children = null, patchFlag = 0, dynamicProps = null, shapeFlag = type === Fragment ? 0 : 1, isBlockNode = false, needFullChildrenNormalization = false) {
    const vnode = {
      __v_isVNode: true,
      __v_skip: true,
      type,
      props,
      key: props && normalizeKey(props),
      ref: props && normalizeRef(props),
      scopeId: currentScopeId,
      slotScopeIds: null,
      children,
      component: null,
      suspense: null,
      ssContent: null,
      ssFallback: null,
      dirs: null,
      transition: null,
      el: null,
      anchor: null,
      target: null,
      targetStart: null,
      targetAnchor: null,
      staticCount: 0,
      shapeFlag,
      patchFlag,
      dynamicProps,
      dynamicChildren: null,
      appContext: null,
      ctx: currentRenderingInstance
    };
    if (needFullChildrenNormalization) {
      normalizeChildren(vnode, children);
      if (shapeFlag & 128) {
        type.normalize(vnode);
      }
    } else if (children) {
      vnode.shapeFlag |= isString$1(children) ? 8 : 16;
    }
    if (isBlockTreeEnabled > 0 && // avoid a block node from tracking itself
    !isBlockNode && // has current parent block
    currentBlock && // presence of a patch flag indicates this node needs patching on updates.
    // component nodes also should always be patched, because even if the
    // component doesn't need to update, it needs to persist the instance on to
    // the next vnode so that it can be properly unmounted later.
    (vnode.patchFlag > 0 || shapeFlag & 6) && // the EVENTS flag is only for hydration and if it is the only flag, the
    // vnode should not be considered dynamic due to handler caching.
    vnode.patchFlag !== 32) {
      currentBlock.push(vnode);
    }
    return vnode;
  }
  const createVNode = _createVNode;
  function _createVNode(type, props = null, children = null, patchFlag = 0, dynamicProps = null, isBlockNode = false) {
    if (!type || type === NULL_DYNAMIC_COMPONENT) {
      type = Comment;
    }
    if (isVNode(type)) {
      const cloned = cloneVNode(
        type,
        props,
        true
        /* mergeRef: true */
      );
      if (children) {
        normalizeChildren(cloned, children);
      }
      if (isBlockTreeEnabled > 0 && !isBlockNode && currentBlock) {
        if (cloned.shapeFlag & 6) {
          currentBlock[currentBlock.indexOf(type)] = cloned;
        } else {
          currentBlock.push(cloned);
        }
      }
      cloned.patchFlag = -2;
      return cloned;
    }
    if (isClassComponent(type)) {
      type = type.__vccOpts;
    }
    if (props) {
      props = guardReactiveProps(props);
      let { class: klass, style } = props;
      if (klass && !isString$1(klass)) {
        props.class = normalizeClass(klass);
      }
      if (isObject(style)) {
        if (isProxy(style) && !isArray$1(style)) {
          style = extend$1({}, style);
        }
        props.style = normalizeStyle(style);
      }
    }
    const shapeFlag = isString$1(type) ? 1 : isSuspense(type) ? 128 : isTeleport(type) ? 64 : isObject(type) ? 4 : isFunction$1(type) ? 2 : 0;
    return createBaseVNode(
      type,
      props,
      children,
      patchFlag,
      dynamicProps,
      shapeFlag,
      isBlockNode,
      true
    );
  }
  function guardReactiveProps(props) {
    if (!props)
      return null;
    return isProxy(props) || isInternalObject(props) ? extend$1({}, props) : props;
  }
  function cloneVNode(vnode, extraProps, mergeRef = false, cloneTransition = false) {
    const { props, ref: ref2, patchFlag, children, transition } = vnode;
    const mergedProps = extraProps ? mergeProps(props || {}, extraProps) : props;
    const cloned = {
      __v_isVNode: true,
      __v_skip: true,
      type: vnode.type,
      props: mergedProps,
      key: mergedProps && normalizeKey(mergedProps),
      ref: extraProps && extraProps.ref ? (
        // #2078 in the case of <component :is="vnode" ref="extra"/>
        // if the vnode itself already has a ref, cloneVNode will need to merge
        // the refs so the single vnode can be set on multiple refs
        mergeRef && ref2 ? isArray$1(ref2) ? ref2.concat(normalizeRef(extraProps)) : [ref2, normalizeRef(extraProps)] : normalizeRef(extraProps)
      ) : ref2,
      scopeId: vnode.scopeId,
      slotScopeIds: vnode.slotScopeIds,
      children,
      target: vnode.target,
      targetStart: vnode.targetStart,
      targetAnchor: vnode.targetAnchor,
      staticCount: vnode.staticCount,
      shapeFlag: vnode.shapeFlag,
      // if the vnode is cloned with extra props, we can no longer assume its
      // existing patch flag to be reliable and need to add the FULL_PROPS flag.
      // note: preserve flag for fragments since they use the flag for children
      // fast paths only.
      patchFlag: extraProps && vnode.type !== Fragment ? patchFlag === -1 ? 16 : patchFlag | 16 : patchFlag,
      dynamicProps: vnode.dynamicProps,
      dynamicChildren: vnode.dynamicChildren,
      appContext: vnode.appContext,
      dirs: vnode.dirs,
      transition,
      // These should technically only be non-null on mounted VNodes. However,
      // they *should* be copied for kept-alive vnodes. So we just always copy
      // them since them being non-null during a mount doesn't affect the logic as
      // they will simply be overwritten.
      component: vnode.component,
      suspense: vnode.suspense,
      ssContent: vnode.ssContent && cloneVNode(vnode.ssContent),
      ssFallback: vnode.ssFallback && cloneVNode(vnode.ssFallback),
      placeholder: vnode.placeholder,
      el: vnode.el,
      anchor: vnode.anchor,
      ctx: vnode.ctx,
      ce: vnode.ce
    };
    if (transition && cloneTransition) {
      setTransitionHooks(
        cloned,
        transition.clone(cloned)
      );
    }
    return cloned;
  }
  function createTextVNode(text = " ", flag = 0) {
    return createVNode(Text, null, text, flag);
  }
  function createStaticVNode(content, numberOfNodes) {
    const vnode = createVNode(Static, null, content);
    vnode.staticCount = numberOfNodes;
    return vnode;
  }
  function createCommentVNode(text = "", asBlock = false) {
    return asBlock ? (openBlock(), createBlock(Comment, null, text)) : createVNode(Comment, null, text);
  }
  function normalizeVNode(child) {
    if (child == null || typeof child === "boolean") {
      return createVNode(Comment);
    } else if (isArray$1(child)) {
      return createVNode(
        Fragment,
        null,
        // #3666, avoid reference pollution when reusing vnode
        child.slice()
      );
    } else if (isVNode(child)) {
      return cloneIfMounted(child);
    } else {
      return createVNode(Text, null, String(child));
    }
  }
  function cloneIfMounted(child) {
    return child.el === null && child.patchFlag !== -1 || child.memo ? child : cloneVNode(child);
  }
  function normalizeChildren(vnode, children) {
    let type = 0;
    const { shapeFlag } = vnode;
    if (children == null) {
      children = null;
    } else if (isArray$1(children)) {
      type = 16;
    } else if (typeof children === "object") {
      if (shapeFlag & (1 | 64)) {
        const slot = children.default;
        if (slot) {
          slot._c && (slot._d = false);
          normalizeChildren(vnode, slot());
          slot._c && (slot._d = true);
        }
        return;
      } else {
        type = 32;
        const slotFlag = children._;
        if (!slotFlag && !isInternalObject(children)) {
          children._ctx = currentRenderingInstance;
        } else if (slotFlag === 3 && currentRenderingInstance) {
          if (currentRenderingInstance.slots._ === 1) {
            children._ = 1;
          } else {
            children._ = 2;
            vnode.patchFlag |= 1024;
          }
        }
      }
    } else if (isFunction$1(children)) {
      children = { default: children, _ctx: currentRenderingInstance };
      type = 32;
    } else {
      children = String(children);
      if (shapeFlag & 64) {
        type = 16;
        children = [createTextVNode(children)];
      } else {
        type = 8;
      }
    }
    vnode.children = children;
    vnode.shapeFlag |= type;
  }
  function mergeProps(...args) {
    const ret = {};
    for (let i = 0; i < args.length; i++) {
      const toMerge = args[i];
      for (const key in toMerge) {
        if (key === "class") {
          if (ret.class !== toMerge.class) {
            ret.class = normalizeClass([ret.class, toMerge.class]);
          }
        } else if (key === "style") {
          ret.style = normalizeStyle([ret.style, toMerge.style]);
        } else if (isOn$1(key)) {
          const existing = ret[key];
          const incoming = toMerge[key];
          if (incoming && existing !== incoming && !(isArray$1(existing) && existing.includes(incoming))) {
            ret[key] = existing ? [].concat(existing, incoming) : incoming;
          }
        } else if (key !== "") {
          ret[key] = toMerge[key];
        }
      }
    }
    return ret;
  }
  function invokeVNodeHook(hook, instance, vnode, prevVNode = null) {
    callWithAsyncErrorHandling(hook, instance, 7, [
      vnode,
      prevVNode
    ]);
  }
  const emptyAppContext = createAppContext();
  let uid = 0;
  function createComponentInstance(vnode, parent, suspense) {
    const type = vnode.type;
    const appContext = (parent ? parent.appContext : vnode.appContext) || emptyAppContext;
    const instance = {
      uid: uid++,
      vnode,
      type,
      parent,
      appContext,
      root: null,
      // to be immediately set
      next: null,
      subTree: null,
      // will be set synchronously right after creation
      effect: null,
      update: null,
      // will be set synchronously right after creation
      job: null,
      scope: new EffectScope(
        true
        /* detached */
      ),
      render: null,
      proxy: null,
      exposed: null,
      exposeProxy: null,
      withProxy: null,
      provides: parent ? parent.provides : Object.create(appContext.provides),
      ids: parent ? parent.ids : ["", 0, 0],
      accessCache: null,
      renderCache: [],
      // local resolved assets
      components: null,
      directives: null,
      // resolved props and emits options
      propsOptions: normalizePropsOptions(type, appContext),
      emitsOptions: normalizeEmitsOptions(type, appContext),
      // emit
      emit: null,
      // to be set immediately
      emitted: null,
      // props default value
      propsDefaults: EMPTY_OBJ,
      // inheritAttrs
      inheritAttrs: type.inheritAttrs,
      // state
      ctx: EMPTY_OBJ,
      data: EMPTY_OBJ,
      props: EMPTY_OBJ,
      attrs: EMPTY_OBJ,
      slots: EMPTY_OBJ,
      refs: EMPTY_OBJ,
      setupState: EMPTY_OBJ,
      setupContext: null,
      // suspense related
      suspense,
      suspenseId: suspense ? suspense.pendingId : 0,
      asyncDep: null,
      asyncResolved: false,
      // lifecycle hooks
      // not using enums here because it results in computed properties
      isMounted: false,
      isUnmounted: false,
      isDeactivated: false,
      bc: null,
      c: null,
      bm: null,
      m: null,
      bu: null,
      u: null,
      um: null,
      bum: null,
      da: null,
      a: null,
      rtg: null,
      rtc: null,
      ec: null,
      sp: null
    };
    {
      instance.ctx = { _: instance };
    }
    instance.root = parent ? parent.root : instance;
    instance.emit = emit.bind(null, instance);
    if (vnode.ce) {
      vnode.ce(instance);
    }
    return instance;
  }
  let currentInstance = null;
  const getCurrentInstance = () => currentInstance || currentRenderingInstance;
  let internalSetCurrentInstance;
  let setInSSRSetupState;
  {
    const g = getGlobalThis();
    const registerGlobalSetter = (key, setter) => {
      let setters;
      if (!(setters = g[key]))
        setters = g[key] = [];
      setters.push(setter);
      return (v) => {
        if (setters.length > 1)
          setters.forEach((set) => set(v));
        else
          setters[0](v);
      };
    };
    internalSetCurrentInstance = registerGlobalSetter(
      `__VUE_INSTANCE_SETTERS__`,
      (v) => currentInstance = v
    );
    setInSSRSetupState = registerGlobalSetter(
      `__VUE_SSR_SETTERS__`,
      (v) => isInSSRComponentSetup = v
    );
  }
  const setCurrentInstance = (instance) => {
    const prev = currentInstance;
    internalSetCurrentInstance(instance);
    instance.scope.on();
    return () => {
      instance.scope.off();
      internalSetCurrentInstance(prev);
    };
  };
  const unsetCurrentInstance = () => {
    currentInstance && currentInstance.scope.off();
    internalSetCurrentInstance(null);
  };
  function isStatefulComponent(instance) {
    return instance.vnode.shapeFlag & 4;
  }
  let isInSSRComponentSetup = false;
  function setupComponent(instance, isSSR = false, optimized = false) {
    isSSR && setInSSRSetupState(isSSR);
    const { props, children } = instance.vnode;
    const isStateful = isStatefulComponent(instance);
    initProps(instance, props, isStateful, isSSR);
    initSlots(instance, children, optimized || isSSR);
    const setupResult = isStateful ? setupStatefulComponent(instance, isSSR) : void 0;
    isSSR && setInSSRSetupState(false);
    return setupResult;
  }
  function setupStatefulComponent(instance, isSSR) {
    const Component = instance.type;
    instance.accessCache = /* @__PURE__ */ Object.create(null);
    instance.proxy = new Proxy(instance.ctx, PublicInstanceProxyHandlers);
    const { setup } = Component;
    if (setup) {
      pauseTracking();
      const setupContext = instance.setupContext = setup.length > 1 ? createSetupContext(instance) : null;
      const reset = setCurrentInstance(instance);
      const setupResult = callWithErrorHandling(
        setup,
        instance,
        0,
        [
          instance.props,
          setupContext
        ]
      );
      const isAsyncSetup = isPromise(setupResult);
      resetTracking();
      reset();
      if ((isAsyncSetup || instance.sp) && !isAsyncWrapper(instance)) {
        markAsyncBoundary(instance);
      }
      if (isAsyncSetup) {
        setupResult.then(unsetCurrentInstance, unsetCurrentInstance);
        if (isSSR) {
          return setupResult.then((resolvedResult) => {
            handleSetupResult(instance, resolvedResult, isSSR);
          }).catch((e) => {
            handleError(e, instance, 0);
          });
        } else {
          instance.asyncDep = setupResult;
        }
      } else {
        handleSetupResult(instance, setupResult, isSSR);
      }
    } else {
      finishComponentSetup(instance, isSSR);
    }
  }
  function handleSetupResult(instance, setupResult, isSSR) {
    if (isFunction$1(setupResult)) {
      if (instance.type.__ssrInlineRender) {
        instance.ssrRender = setupResult;
      } else {
        instance.render = setupResult;
      }
    } else if (isObject(setupResult)) {
      instance.setupState = proxyRefs(setupResult);
    } else
      ;
    finishComponentSetup(instance, isSSR);
  }
  let compile;
  function finishComponentSetup(instance, isSSR, skipOptions) {
    const Component = instance.type;
    if (!instance.render) {
      if (!isSSR && compile && !Component.render) {
        const template = Component.template || resolveMergedOptions(instance).template;
        if (template) {
          const { isCustomElement, compilerOptions } = instance.appContext.config;
          const { delimiters, compilerOptions: componentCompilerOptions } = Component;
          const finalCompilerOptions = extend$1(
            extend$1(
              {
                isCustomElement,
                delimiters
              },
              compilerOptions
            ),
            componentCompilerOptions
          );
          Component.render = compile(template, finalCompilerOptions);
        }
      }
      instance.render = Component.render || NOOP;
    }
    {
      const reset = setCurrentInstance(instance);
      pauseTracking();
      try {
        applyOptions(instance);
      } finally {
        resetTracking();
        reset();
      }
    }
  }
  const attrsProxyHandlers = {
    get(target, key) {
      track(target, "get", "");
      return target[key];
    }
  };
  function createSetupContext(instance) {
    const expose = (exposed) => {
      instance.exposed = exposed || {};
    };
    {
      return {
        attrs: new Proxy(instance.attrs, attrsProxyHandlers),
        slots: instance.slots,
        emit: instance.emit,
        expose
      };
    }
  }
  function getComponentPublicInstance(instance) {
    if (instance.exposed) {
      return instance.exposeProxy || (instance.exposeProxy = new Proxy(proxyRefs(markRaw(instance.exposed)), {
        get(target, key) {
          if (key in target) {
            return target[key];
          } else if (key in publicPropertiesMap) {
            return publicPropertiesMap[key](instance);
          }
        },
        has(target, key) {
          return key in target || key in publicPropertiesMap;
        }
      }));
    } else {
      return instance.proxy;
    }
  }
  const classifyRE = /(?:^|[-_])\w/g;
  const classify = (str) => str.replace(classifyRE, (c) => c.toUpperCase()).replace(/[-_]/g, "");
  function getComponentName(Component, includeInferred = true) {
    return isFunction$1(Component) ? Component.displayName || Component.name : Component.name || includeInferred && Component.__name;
  }
  function formatComponentName(instance, Component, isRoot = false) {
    let name = getComponentName(Component);
    if (!name && Component.__file) {
      const match = Component.__file.match(/([^/\\]+)\.\w+$/);
      if (match) {
        name = match[1];
      }
    }
    if (!name && instance && instance.parent) {
      const inferFromRegistry = (registry) => {
        for (const key in registry) {
          if (registry[key] === Component) {
            return key;
          }
        }
      };
      name = inferFromRegistry(
        instance.components || instance.parent.type.components
      ) || inferFromRegistry(instance.appContext.components);
    }
    return name ? classify(name) : isRoot ? `App` : `Anonymous`;
  }
  function isClassComponent(value) {
    return isFunction$1(value) && "__vccOpts" in value;
  }
  const computed = (getterOrOptions, debugOptions) => {
    const c = computed$1(getterOrOptions, debugOptions, isInSSRComponentSetup);
    return c;
  };
  const version = "3.5.22";
  /**
  * @vue/shared v3.5.22
  * (c) 2018-present Yuxi (Evan) You and Vue contributors
  * @license MIT
  **/
  // @__NO_SIDE_EFFECTS__
  function makeMap(str) {
    const map = /* @__PURE__ */ Object.create(null);
    for (const key of str.split(","))
      map[key] = 1;
    return (val) => val in map;
  }
  const isOn = (key) => key.charCodeAt(0) === 111 && key.charCodeAt(1) === 110 && // uppercase letter
  (key.charCodeAt(2) > 122 || key.charCodeAt(2) < 97);
  const isModelListener = (key) => key.startsWith("onUpdate:");
  const extend = Object.assign;
  const hasOwnProperty = Object.prototype.hasOwnProperty;
  const hasOwn = (val, key) => hasOwnProperty.call(val, key);
  const isArray = Array.isArray;
  const isFunction = (val) => typeof val === "function";
  const isString = (val) => typeof val === "string";
  const isSymbol = (val) => typeof val === "symbol";
  const objectToString = Object.prototype.toString;
  const toTypeString = (value) => objectToString.call(value);
  const isPlainObject = (val) => toTypeString(val) === "[object Object]";
  const cacheStringFunction = (fn) => {
    const cache = /* @__PURE__ */ Object.create(null);
    return (str) => {
      const hit = cache[str];
      return hit || (cache[str] = fn(str));
    };
  };
  const camelizeRE = /-\w/g;
  const camelize = cacheStringFunction(
    (str) => {
      return str.replace(camelizeRE, (c) => c.slice(1).toUpperCase());
    }
  );
  const hyphenateRE = /\B([A-Z])/g;
  const hyphenate = cacheStringFunction(
    (str) => str.replace(hyphenateRE, "-$1").toLowerCase()
  );
  const capitalize = cacheStringFunction((str) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
  });
  const toNumber = (val) => {
    const n = isString(val) ? Number(val) : NaN;
    return isNaN(n) ? val : n;
  };
  const specialBooleanAttrs = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
  const isSpecialBooleanAttr = /* @__PURE__ */ makeMap(specialBooleanAttrs);
  function includeBooleanAttr(value) {
    return !!value || value === "";
  }
  /**
  * @vue/runtime-dom v3.5.22
  * (c) 2018-present Yuxi (Evan) You and Vue contributors
  * @license MIT
  **/
  let policy = void 0;
  const tt = typeof window !== "undefined" && window.trustedTypes;
  if (tt) {
    try {
      policy = /* @__PURE__ */ tt.createPolicy("vue", {
        createHTML: (val) => val
      });
    } catch (e) {
    }
  }
  const unsafeToTrustedHTML = policy ? (val) => policy.createHTML(val) : (val) => val;
  const svgNS = "http://www.w3.org/2000/svg";
  const mathmlNS = "http://www.w3.org/1998/Math/MathML";
  const doc = typeof document !== "undefined" ? document : null;
  const templateContainer = doc && /* @__PURE__ */ doc.createElement("template");
  const nodeOps = {
    insert: (child, parent, anchor) => {
      parent.insertBefore(child, anchor || null);
    },
    remove: (child) => {
      const parent = child.parentNode;
      if (parent) {
        parent.removeChild(child);
      }
    },
    createElement: (tag, namespace, is, props) => {
      const el = namespace === "svg" ? doc.createElementNS(svgNS, tag) : namespace === "mathml" ? doc.createElementNS(mathmlNS, tag) : is ? doc.createElement(tag, { is }) : doc.createElement(tag);
      if (tag === "select" && props && props.multiple != null) {
        el.setAttribute("multiple", props.multiple);
      }
      return el;
    },
    createText: (text) => doc.createTextNode(text),
    createComment: (text) => doc.createComment(text),
    setText: (node, text) => {
      node.nodeValue = text;
    },
    setElementText: (el, text) => {
      el.textContent = text;
    },
    parentNode: (node) => node.parentNode,
    nextSibling: (node) => node.nextSibling,
    querySelector: (selector) => doc.querySelector(selector),
    setScopeId(el, id) {
      el.setAttribute(id, "");
    },
    // __UNSAFE__
    // Reason: innerHTML.
    // Static content here can only come from compiled templates.
    // As long as the user only uses trusted templates, this is safe.
    insertStaticContent(content, parent, anchor, namespace, start, end) {
      const before = anchor ? anchor.previousSibling : parent.lastChild;
      if (start && (start === end || start.nextSibling)) {
        while (true) {
          parent.insertBefore(start.cloneNode(true), anchor);
          if (start === end || !(start = start.nextSibling))
            break;
        }
      } else {
        templateContainer.innerHTML = unsafeToTrustedHTML(
          namespace === "svg" ? `<svg>${content}</svg>` : namespace === "mathml" ? `<math>${content}</math>` : content
        );
        const template = templateContainer.content;
        if (namespace === "svg" || namespace === "mathml") {
          const wrapper = template.firstChild;
          while (wrapper.firstChild) {
            template.appendChild(wrapper.firstChild);
          }
          template.removeChild(wrapper);
        }
        parent.insertBefore(template, anchor);
      }
      return [
        // first
        before ? before.nextSibling : parent.firstChild,
        // last
        anchor ? anchor.previousSibling : parent.lastChild
      ];
    }
  };
  const vtcKey = Symbol("_vtc");
  function patchClass(el, value, isSVG) {
    const transitionClasses = el[vtcKey];
    if (transitionClasses) {
      value = (value ? [value, ...transitionClasses] : [...transitionClasses]).join(" ");
    }
    if (value == null) {
      el.removeAttribute("class");
    } else if (isSVG) {
      el.setAttribute("class", value);
    } else {
      el.className = value;
    }
  }
  const vShowOriginalDisplay = Symbol("_vod");
  const vShowHidden = Symbol("_vsh");
  const CSS_VAR_TEXT = Symbol("");
  const displayRE = /(?:^|;)\s*display\s*:/;
  function patchStyle(el, prev, next) {
    const style = el.style;
    const isCssString = isString(next);
    let hasControlledDisplay = false;
    if (next && !isCssString) {
      if (prev) {
        if (!isString(prev)) {
          for (const key in prev) {
            if (next[key] == null) {
              setStyle(style, key, "");
            }
          }
        } else {
          for (const prevStyle of prev.split(";")) {
            const key = prevStyle.slice(0, prevStyle.indexOf(":")).trim();
            if (next[key] == null) {
              setStyle(style, key, "");
            }
          }
        }
      }
      for (const key in next) {
        if (key === "display") {
          hasControlledDisplay = true;
        }
        setStyle(style, key, next[key]);
      }
    } else {
      if (isCssString) {
        if (prev !== next) {
          const cssVarText = style[CSS_VAR_TEXT];
          if (cssVarText) {
            next += ";" + cssVarText;
          }
          style.cssText = next;
          hasControlledDisplay = displayRE.test(next);
        }
      } else if (prev) {
        el.removeAttribute("style");
      }
    }
    if (vShowOriginalDisplay in el) {
      el[vShowOriginalDisplay] = hasControlledDisplay ? style.display : "";
      if (el[vShowHidden]) {
        style.display = "none";
      }
    }
  }
  const importantRE = /\s*!important$/;
  function setStyle(style, name, val) {
    if (isArray(val)) {
      val.forEach((v) => setStyle(style, name, v));
    } else {
      if (val == null)
        val = "";
      if (name.startsWith("--")) {
        style.setProperty(name, val);
      } else {
        const prefixed = autoPrefix(style, name);
        if (importantRE.test(val)) {
          style.setProperty(
            hyphenate(prefixed),
            val.replace(importantRE, ""),
            "important"
          );
        } else {
          style[prefixed] = val;
        }
      }
    }
  }
  const prefixes = ["Webkit", "Moz", "ms"];
  const prefixCache = {};
  function autoPrefix(style, rawName) {
    const cached = prefixCache[rawName];
    if (cached) {
      return cached;
    }
    let name = camelize$1(rawName);
    if (name !== "filter" && name in style) {
      return prefixCache[rawName] = name;
    }
    name = capitalize(name);
    for (let i = 0; i < prefixes.length; i++) {
      const prefixed = prefixes[i] + name;
      if (prefixed in style) {
        return prefixCache[rawName] = prefixed;
      }
    }
    return rawName;
  }
  const xlinkNS = "http://www.w3.org/1999/xlink";
  function patchAttr(el, key, value, isSVG, instance, isBoolean = isSpecialBooleanAttr(key)) {
    if (isSVG && key.startsWith("xlink:")) {
      if (value == null) {
        el.removeAttributeNS(xlinkNS, key.slice(6, key.length));
      } else {
        el.setAttributeNS(xlinkNS, key, value);
      }
    } else {
      if (value == null || isBoolean && !includeBooleanAttr(value)) {
        el.removeAttribute(key);
      } else {
        el.setAttribute(
          key,
          isBoolean ? "" : isSymbol(value) ? String(value) : value
        );
      }
    }
  }
  function patchDOMProp(el, key, value, parentComponent, attrName) {
    if (key === "innerHTML" || key === "textContent") {
      if (value != null) {
        el[key] = key === "innerHTML" ? unsafeToTrustedHTML(value) : value;
      }
      return;
    }
    const tag = el.tagName;
    if (key === "value" && tag !== "PROGRESS" && // custom elements may use _value internally
    !tag.includes("-")) {
      const oldValue = tag === "OPTION" ? el.getAttribute("value") || "" : el.value;
      const newValue = value == null ? (
        // #11647: value should be set as empty string for null and undefined,
        // but <input type="checkbox"> should be set as 'on'.
        el.type === "checkbox" ? "on" : ""
      ) : String(value);
      if (oldValue !== newValue || !("_value" in el)) {
        el.value = newValue;
      }
      if (value == null) {
        el.removeAttribute(key);
      }
      el._value = value;
      return;
    }
    let needRemove = false;
    if (value === "" || value == null) {
      const type = typeof el[key];
      if (type === "boolean") {
        value = includeBooleanAttr(value);
      } else if (value == null && type === "string") {
        value = "";
        needRemove = true;
      } else if (type === "number") {
        value = 0;
        needRemove = true;
      }
    }
    try {
      el[key] = value;
    } catch (e) {
    }
    needRemove && el.removeAttribute(attrName || key);
  }
  function addEventListener(el, event, handler, options) {
    el.addEventListener(event, handler, options);
  }
  function removeEventListener(el, event, handler, options) {
    el.removeEventListener(event, handler, options);
  }
  const veiKey = Symbol("_vei");
  function patchEvent(el, rawName, prevValue, nextValue, instance = null) {
    const invokers = el[veiKey] || (el[veiKey] = {});
    const existingInvoker = invokers[rawName];
    if (nextValue && existingInvoker) {
      existingInvoker.value = nextValue;
    } else {
      const [name, options] = parseName(rawName);
      if (nextValue) {
        const invoker = invokers[rawName] = createInvoker(
          nextValue,
          instance
        );
        addEventListener(el, name, invoker, options);
      } else if (existingInvoker) {
        removeEventListener(el, name, existingInvoker, options);
        invokers[rawName] = void 0;
      }
    }
  }
  const optionsModifierRE = /(?:Once|Passive|Capture)$/;
  function parseName(name) {
    let options;
    if (optionsModifierRE.test(name)) {
      options = {};
      let m;
      while (m = name.match(optionsModifierRE)) {
        name = name.slice(0, name.length - m[0].length);
        options[m[0].toLowerCase()] = true;
      }
    }
    const event = name[2] === ":" ? name.slice(3) : hyphenate(name.slice(2));
    return [event, options];
  }
  let cachedNow = 0;
  const p = /* @__PURE__ */ Promise.resolve();
  const getNow = () => cachedNow || (p.then(() => cachedNow = 0), cachedNow = Date.now());
  function createInvoker(initialValue, instance) {
    const invoker = (e) => {
      if (!e._vts) {
        e._vts = Date.now();
      } else if (e._vts <= invoker.attached) {
        return;
      }
      callWithAsyncErrorHandling(
        patchStopImmediatePropagation(e, invoker.value),
        instance,
        5,
        [e]
      );
    };
    invoker.value = initialValue;
    invoker.attached = getNow();
    return invoker;
  }
  function patchStopImmediatePropagation(e, value) {
    if (isArray(value)) {
      const originalStop = e.stopImmediatePropagation;
      e.stopImmediatePropagation = () => {
        originalStop.call(e);
        e._stopped = true;
      };
      return value.map(
        (fn) => (e2) => !e2._stopped && fn && fn(e2)
      );
    } else {
      return value;
    }
  }
  const isNativeOn = (key) => key.charCodeAt(0) === 111 && key.charCodeAt(1) === 110 && // lowercase letter
  key.charCodeAt(2) > 96 && key.charCodeAt(2) < 123;
  const patchProp = (el, key, prevValue, nextValue, namespace, parentComponent) => {
    const isSVG = namespace === "svg";
    if (key === "class") {
      patchClass(el, nextValue, isSVG);
    } else if (key === "style") {
      patchStyle(el, prevValue, nextValue);
    } else if (isOn(key)) {
      if (!isModelListener(key)) {
        patchEvent(el, key, prevValue, nextValue, parentComponent);
      }
    } else if (key[0] === "." ? (key = key.slice(1), true) : key[0] === "^" ? (key = key.slice(1), false) : shouldSetAsProp(el, key, nextValue, isSVG)) {
      patchDOMProp(el, key, nextValue);
      if (!el.tagName.includes("-") && (key === "value" || key === "checked" || key === "selected")) {
        patchAttr(el, key, nextValue, isSVG, parentComponent, key !== "value");
      }
    } else if (
      // #11081 force set props for possible async custom element
      el._isVueCE && (/[A-Z]/.test(key) || !isString(nextValue))
    ) {
      patchDOMProp(el, camelize(key), nextValue, parentComponent, key);
    } else {
      if (key === "true-value") {
        el._trueValue = nextValue;
      } else if (key === "false-value") {
        el._falseValue = nextValue;
      }
      patchAttr(el, key, nextValue, isSVG);
    }
  };
  function shouldSetAsProp(el, key, value, isSVG) {
    if (isSVG) {
      if (key === "innerHTML" || key === "textContent") {
        return true;
      }
      if (key in el && isNativeOn(key) && isFunction(value)) {
        return true;
      }
      return false;
    }
    if (key === "spellcheck" || key === "draggable" || key === "translate" || key === "autocorrect") {
      return false;
    }
    if (key === "form") {
      return false;
    }
    if (key === "list" && el.tagName === "INPUT") {
      return false;
    }
    if (key === "type" && el.tagName === "TEXTAREA") {
      return false;
    }
    if (key === "width" || key === "height") {
      const tag = el.tagName;
      if (tag === "IMG" || tag === "VIDEO" || tag === "CANVAS" || tag === "SOURCE") {
        return false;
      }
    }
    if (isNativeOn(key) && isString(value)) {
      return false;
    }
    return key in el;
  }
  const REMOVAL = {};
  // @__NO_SIDE_EFFECTS__
  function defineCustomElement(options, extraOptions, _createApp) {
    let Comp = /* @__PURE__ */ defineComponent(options, extraOptions);
    if (isPlainObject(Comp))
      Comp = extend({}, Comp, extraOptions);
    class VueCustomElement extends VueElement {
      constructor(initialProps) {
        super(Comp, initialProps, _createApp);
      }
    }
    VueCustomElement.def = Comp;
    return VueCustomElement;
  }
  const BaseClass = typeof HTMLElement !== "undefined" ? HTMLElement : class {
  };
  class VueElement extends BaseClass {
    constructor(_def, _props = {}, _createApp = createApp) {
      super();
      this._def = _def;
      this._props = _props;
      this._createApp = _createApp;
      this._isVueCE = true;
      this._instance = null;
      this._app = null;
      this._nonce = this._def.nonce;
      this._connected = false;
      this._resolved = false;
      this._numberProps = null;
      this._styleChildren = /* @__PURE__ */ new WeakSet();
      this._ob = null;
      if (this.shadowRoot && _createApp !== createApp) {
        this._root = this.shadowRoot;
      } else {
        if (_def.shadowRoot !== false) {
          this.attachShadow(
            extend({}, _def.shadowRootOptions, {
              mode: "open"
            })
          );
          this._root = this.shadowRoot;
        } else {
          this._root = this;
        }
      }
    }
    connectedCallback() {
      if (!this.isConnected)
        return;
      if (!this.shadowRoot && !this._resolved) {
        this._parseSlots();
      }
      this._connected = true;
      let parent = this;
      while (parent = parent && (parent.parentNode || parent.host)) {
        if (parent instanceof VueElement) {
          this._parent = parent;
          break;
        }
      }
      if (!this._instance) {
        if (this._resolved) {
          this._mount(this._def);
        } else {
          if (parent && parent._pendingResolve) {
            this._pendingResolve = parent._pendingResolve.then(() => {
              this._pendingResolve = void 0;
              this._resolveDef();
            });
          } else {
            this._resolveDef();
          }
        }
      }
    }
    _setParent(parent = this._parent) {
      if (parent) {
        this._instance.parent = parent._instance;
        this._inheritParentContext(parent);
      }
    }
    _inheritParentContext(parent = this._parent) {
      if (parent && this._app) {
        Object.setPrototypeOf(
          this._app._context.provides,
          parent._instance.provides
        );
      }
    }
    disconnectedCallback() {
      this._connected = false;
      nextTick(() => {
        if (!this._connected) {
          if (this._ob) {
            this._ob.disconnect();
            this._ob = null;
          }
          this._app && this._app.unmount();
          if (this._instance)
            this._instance.ce = void 0;
          this._app = this._instance = null;
          if (this._teleportTargets) {
            this._teleportTargets.clear();
            this._teleportTargets = void 0;
          }
        }
      });
    }
    _processMutations(mutations) {
      for (const m of mutations) {
        this._setAttr(m.attributeName);
      }
    }
    /**
     * resolve inner component definition (handle possible async component)
     */
    _resolveDef() {
      if (this._pendingResolve) {
        return;
      }
      for (let i = 0; i < this.attributes.length; i++) {
        this._setAttr(this.attributes[i].name);
      }
      this._ob = new MutationObserver(this._processMutations.bind(this));
      this._ob.observe(this, { attributes: true });
      const resolve = (def2, isAsync = false) => {
        this._resolved = true;
        this._pendingResolve = void 0;
        const { props, styles } = def2;
        let numberProps;
        if (props && !isArray(props)) {
          for (const key in props) {
            const opt = props[key];
            if (opt === Number || opt && opt.type === Number) {
              if (key in this._props) {
                this._props[key] = toNumber(this._props[key]);
              }
              (numberProps || (numberProps = /* @__PURE__ */ Object.create(null)))[camelize(key)] = true;
            }
          }
        }
        this._numberProps = numberProps;
        this._resolveProps(def2);
        if (this.shadowRoot) {
          this._applyStyles(styles);
        }
        this._mount(def2);
      };
      const asyncDef = this._def.__asyncLoader;
      if (asyncDef) {
        this._pendingResolve = asyncDef().then((def2) => {
          def2.configureApp = this._def.configureApp;
          resolve(this._def = def2, true);
        });
      } else {
        resolve(this._def);
      }
    }
    _mount(def2) {
      this._app = this._createApp(def2);
      this._inheritParentContext();
      if (def2.configureApp) {
        def2.configureApp(this._app);
      }
      this._app._ceVNode = this._createVNode();
      this._app.mount(this._root);
      const exposed = this._instance && this._instance.exposed;
      if (!exposed)
        return;
      for (const key in exposed) {
        if (!hasOwn(this, key)) {
          Object.defineProperty(this, key, {
            // unwrap ref to be consistent with public instance behavior
            get: () => unref(exposed[key])
          });
        }
      }
    }
    _resolveProps(def2) {
      const { props } = def2;
      const declaredPropKeys = isArray(props) ? props : Object.keys(props || {});
      for (const key of Object.keys(this)) {
        if (key[0] !== "_" && declaredPropKeys.includes(key)) {
          this._setProp(key, this[key]);
        }
      }
      for (const key of declaredPropKeys.map(camelize)) {
        Object.defineProperty(this, key, {
          get() {
            return this._getProp(key);
          },
          set(val) {
            this._setProp(key, val, true, true);
          }
        });
      }
    }
    _setAttr(key) {
      if (key.startsWith("data-v-"))
        return;
      const has = this.hasAttribute(key);
      let value = has ? this.getAttribute(key) : REMOVAL;
      const camelKey = camelize(key);
      if (has && this._numberProps && this._numberProps[camelKey]) {
        value = toNumber(value);
      }
      this._setProp(camelKey, value, false, true);
    }
    /**
     * @internal
     */
    _getProp(key) {
      return this._props[key];
    }
    /**
     * @internal
     */
    _setProp(key, val, shouldReflect = true, shouldUpdate = false) {
      if (val !== this._props[key]) {
        if (val === REMOVAL) {
          delete this._props[key];
        } else {
          this._props[key] = val;
          if (key === "key" && this._app) {
            this._app._ceVNode.key = val;
          }
        }
        if (shouldUpdate && this._instance) {
          this._update();
        }
        if (shouldReflect) {
          const ob = this._ob;
          if (ob) {
            this._processMutations(ob.takeRecords());
            ob.disconnect();
          }
          if (val === true) {
            this.setAttribute(hyphenate(key), "");
          } else if (typeof val === "string" || typeof val === "number") {
            this.setAttribute(hyphenate(key), val + "");
          } else if (!val) {
            this.removeAttribute(hyphenate(key));
          }
          ob && ob.observe(this, { attributes: true });
        }
      }
    }
    _update() {
      const vnode = this._createVNode();
      if (this._app)
        vnode.appContext = this._app._context;
      render(vnode, this._root);
    }
    _createVNode() {
      const baseProps = {};
      if (!this.shadowRoot) {
        baseProps.onVnodeMounted = baseProps.onVnodeUpdated = this._renderSlots.bind(this);
      }
      const vnode = createVNode(this._def, extend(baseProps, this._props));
      if (!this._instance) {
        vnode.ce = (instance) => {
          this._instance = instance;
          instance.ce = this;
          instance.isCE = true;
          const dispatch = (event, args) => {
            this.dispatchEvent(
              new CustomEvent(
                event,
                isPlainObject(args[0]) ? extend({ detail: args }, args[0]) : { detail: args }
              )
            );
          };
          instance.emit = (event, ...args) => {
            dispatch(event, args);
            if (hyphenate(event) !== event) {
              dispatch(hyphenate(event), args);
            }
          };
          this._setParent();
        };
      }
      return vnode;
    }
    _applyStyles(styles, owner) {
      if (!styles)
        return;
      if (owner) {
        if (owner === this._def || this._styleChildren.has(owner)) {
          return;
        }
        this._styleChildren.add(owner);
      }
      const nonce = this._nonce;
      for (let i = styles.length - 1; i >= 0; i--) {
        const s = document.createElement("style");
        if (nonce)
          s.setAttribute("nonce", nonce);
        s.textContent = styles[i];
        this.shadowRoot.prepend(s);
      }
    }
    /**
     * Only called when shadowRoot is false
     */
    _parseSlots() {
      const slots = this._slots = {};
      let n;
      while (n = this.firstChild) {
        const slotName = n.nodeType === 1 && n.getAttribute("slot") || "default";
        (slots[slotName] || (slots[slotName] = [])).push(n);
        this.removeChild(n);
      }
    }
    /**
     * Only called when shadowRoot is false
     */
    _renderSlots() {
      const outlets = this._getSlots();
      const scopeId = this._instance.type.__scopeId;
      for (let i = 0; i < outlets.length; i++) {
        const o = outlets[i];
        const slotName = o.getAttribute("name") || "default";
        const content = this._slots[slotName];
        const parent = o.parentNode;
        if (content) {
          for (const n of content) {
            if (scopeId && n.nodeType === 1) {
              const id = scopeId + "-s";
              const walker = document.createTreeWalker(n, 1);
              n.setAttribute(id, "");
              let child;
              while (child = walker.nextNode()) {
                child.setAttribute(id, "");
              }
            }
            parent.insertBefore(n, o);
          }
        } else {
          while (o.firstChild)
            parent.insertBefore(o.firstChild, o);
        }
        parent.removeChild(o);
      }
    }
    /**
     * @internal
     */
    _getSlots() {
      const roots = [this];
      if (this._teleportTargets) {
        roots.push(...this._teleportTargets);
      }
      return roots.reduce((res, i) => {
        res.push(...Array.from(i.querySelectorAll("slot")));
        return res;
      }, []);
    }
    /**
     * @internal
     */
    _injectChildStyle(comp) {
      this._applyStyles(comp.styles, comp);
    }
    /**
     * @internal
     */
    _removeChildStyle(comp) {
    }
  }
  const rendererOptions = /* @__PURE__ */ extend({ patchProp }, nodeOps);
  let renderer;
  function ensureRenderer() {
    return renderer || (renderer = createRenderer(rendererOptions));
  }
  const render = (...args) => {
    ensureRenderer().render(...args);
  };
  const createApp = (...args) => {
    const app = ensureRenderer().createApp(...args);
    const { mount } = app;
    app.mount = (containerOrSelector) => {
      const container = normalizeContainer(containerOrSelector);
      if (!container)
        return;
      const component = app._component;
      if (!isFunction(component) && !component.render && !component.template) {
        component.template = container.innerHTML;
      }
      if (container.nodeType === 1) {
        container.textContent = "";
      }
      const proxy = mount(container, false, resolveRootNamespace(container));
      if (container instanceof Element) {
        container.removeAttribute("v-cloak");
        container.setAttribute("data-v-app", "");
      }
      return proxy;
    };
    return app;
  };
  function resolveRootNamespace(container) {
    if (container instanceof SVGElement) {
      return "svg";
    }
    if (typeof MathMLElement === "function" && container instanceof MathMLElement) {
      return "mathml";
    }
  }
  function normalizeContainer(container) {
    if (isString(container)) {
      const res = document.querySelector(container);
      return res;
    }
    return container;
  }
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _sfc_main = /* @__PURE__ */ defineComponent({
    name: "EnjoyHen",
    props: {
      heygenApiKey: {
        type: String,
        default: ""
      },
      heygenServerUrl: {
        type: String,
        default: "https://api.heygen.com"
      },
      locale: {
        type: String,
        default: "it-IT"
      },
      teamLogo: {
        type: String,
        default: "/images/logoai.jpeg"
      },
      teamSlug: {
        type: String,
        default: () => window.location.pathname.split("/").pop()
      }
    },
    data() {
      return {
        // Variabili locali (non props)
        uuid: null,
        heygenAvatar: "",
        heygenVoice: "",
        threadId: null,
        teamSlugLocal: null,
        heygenVideo: null,
        textInput: null,
        sendBtn: null,
        micBtn: null,
        startChatBtn: null,
        startChatContainer: null,
        listeningBadge: null,
        loadingOverlay: null,
        videoAvatarStatus: null,
        feedbackMsg: null,
        isListening: false,
        recognition: null,
        audioCtx: null,
        currentEventSource: null,
        HEYGEN_CONFIG: {
          apiKey: "",
          serverUrl: "https://api.heygen.com"
        },
        heygen: {
          sessionInfo: null,
          room: null,
          mediaStream: null,
          sessionToken: null,
          connecting: false,
          started: false
        }
      };
    },
    mounted() {
      try {
        this.initComponent();
      } catch (e) {
        console.error("EnjoyHen mount error:", e);
      }
    },
    beforeUnmount() {
      try {
        this.cleanup();
      } catch {
      }
    },
    methods: {
      initComponent() {
        var _a, _b, _c, _d;
        const $ = (id) => document.getElementById(id);
        this.heygenVideo = $("heygenVideo");
        this.textInput = $("textInput");
        this.sendBtn = $("sendBtn");
        this.micBtn = $("micBtn");
        this.startChatBtn = $("startChatBtn");
        this.startChatContainer = $("startChatContainer");
        this.listeningBadge = $("listeningBadge");
        this.loadingOverlay = $("loadingOverlay");
        this.videoAvatarStatus = $("videoAvatarStatus");
        this.feedbackMsg = $("feedbackMsg");
        const urlParams = new URLSearchParams(window.location.search);
        this.uuid = urlParams.get("uuid");
        const teamSlug = this.teamSlug || window.location.pathname.split("/").pop();
        this.heygenAvatar = (urlParams.get("avatar") || "").trim();
        this.heygenVoice = (urlParams.get("voice") || "").trim();
        this.HEYGEN_CONFIG = {
          apiKey: this.heygenApiKey || ((_b = (_a = document.getElementById("enjoyHeyRoot")) == null ? void 0 : _a.dataset) == null ? void 0 : _b.heygenApiKey) || "",
          serverUrl: this.heygenServerUrl || ((_d = (_c = document.getElementById("enjoyHeyRoot")) == null ? void 0 : _c.dataset) == null ? void 0 : _d.heygenServerUrl) || "https://api.heygen.com"
        };
        this.heygen = {
          sessionInfo: null,
          room: null,
          mediaStream: null,
          sessionToken: null,
          connecting: false,
          started: false
        };
        this.teamSlugLocal = teamSlug;
        this.setupEventListeners();
        this.showStartChatButton();
      },
      setupEventListeners() {
        if (this.sendBtn) {
          this.sendBtn.addEventListener("click", () => this.onSend());
        }
        if (this.textInput) {
          this.textInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter")
              this.onSend();
          });
        }
        if (this.micBtn) {
          this.micBtn.addEventListener("click", () => this.onMicClick());
        }
        if (this.startChatBtn) {
          this.startChatBtn.addEventListener("click", () => this.startChat());
        }
      },
      showStartChatButton() {
        if (this.startChatContainer) {
          this.startChatContainer.classList.remove("hidden");
        }
        if (this.loadingOverlay) {
          this.loadingOverlay.classList.add("hidden");
        }
      },
      startChat() {
        if (this.startChatContainer) {
          this.startChatContainer.classList.add("hidden");
        }
        this.ensureHeyGenSession();
      },
      async onMicClick() {
        var _a, _b;
        if (this.isListening && this.recognition) {
          try {
            this.recognition.stop();
            (_b = (_a = this.recognition).abort) == null ? void 0 : _b.call(_a);
          } catch {
          }
          this.isListening = false;
          this.setListeningUI(false);
          console.log("MIC: listening stopped by user");
          return;
        }
        try {
          if (!this.audioCtx) {
            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          }
          if (this.audioCtx.state === "suspended") {
            await this.audioCtx.resume();
          }
        } catch (e) {
          console.warn("AUDIO: failed to init/resume", e);
        }
        const ok = await this.ensureMicPermission();
        if (!ok) {
          alert("Permesso microfono negato. Abilitalo nelle impostazioni del browser.");
          return;
        }
        try {
          const Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
          if (!Rec)
            throw new Error("Web Speech API non disponibile");
          this.recognition = new Rec();
          this.recognition.lang = this.locale || "it-IT";
          this.recognition.interimResults = false;
          this.recognition.continuous = false;
          this.recognition.maxAlternatives = 1;
          this.recognition.onstart = async () => {
            this.isListening = true;
            this.setListeningUI(true);
            console.log("SPEECH: onstart");
          };
          this.recognition.onspeechstart = () => {
            console.log("SPEECH: onspeechstart");
          };
          this.recognition.onspeechend = () => {
            console.log("SPEECH: onspeechend");
          };
          this.recognition.onerror = async (e) => {
            console.error("SPEECH: onerror", e && (e.error || e.message) || e);
            this.isListening = false;
            this.setListeningUI(false);
          };
          this.recognition.onend = async () => {
            this.isListening = false;
            this.setListeningUI(false);
            console.log("SPEECH: onend");
          };
          this.recognition.onresult = (event) => {
            try {
              const res = event.results;
              const last = res[res.length - 1];
              const transcript = last[0].transcript;
              const isFinal = last.isFinal === true || !this.recognition.interimResults;
              console.log("SPEECH: onresult", { transcript, isFinal });
              if (isFinal) {
                this.isListening = false;
                this.setListeningUI(false);
                const safe = (transcript || "").trim();
                if (!safe) {
                  console.warn("SPEECH: final transcript empty, not starting stream");
                  return;
                }
                this.startStream(safe);
              }
            } catch (err) {
              console.error("SPEECH: onresult handler failed", err);
            }
          };
          console.log("SPEECH: start()", { lang: this.recognition.lang });
          this.recognition.start();
        } catch (err) {
          console.warn("Riconoscimento vocale non disponibile o errore", err);
          alert("Riconoscimento vocale non disponibile in questo browser.");
        }
      },
      async ensureMicPermission() {
        try {
          if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia)
            return true;
          console.log("MIC: requesting permission");
          const stream = await navigator.mediaDevices.getUserMedia({
            audio: { echoCancellation: true }
          });
          try {
            stream.getTracks().forEach((t) => t.stop());
          } catch {
          }
          console.log("MIC: permission OK");
          return true;
        } catch (e) {
          console.warn("Mic permission denied or error", e);
          return false;
        }
      },
      setListeningUI(active) {
        if (active) {
          if (this.listeningBadge) {
            this.listeningBadge.classList.remove("hidden");
          }
          if (this.micBtn) {
            this.micBtn.classList.remove("bg-rose-600");
            this.micBtn.classList.add(
              "bg-emerald-600",
              "ring-2",
              "ring-emerald-400",
              "animate-pulse"
            );
          }
        } else {
          if (this.listeningBadge) {
            this.listeningBadge.classList.add("hidden");
          }
          if (this.micBtn) {
            this.micBtn.classList.add("bg-rose-600");
            this.micBtn.classList.remove(
              "bg-emerald-600",
              "ring-2",
              "ring-emerald-400",
              "animate-pulse"
            );
          }
        }
      },
      async onSend() {
        var _a;
        const message = (((_a = this.textInput) == null ? void 0 : _a.value) || "").trim();
        if (!message)
          return;
        this.textInput.value = "";
        this.textInput.disabled = true;
        this.sendBtn.disabled = true;
        this.setFeedback("Invio in corso...");
        try {
          await this.startStream(message);
        } catch (e) {
          console.error("Error starting stream:", e);
          this.setFeedback("❌ Errore nella comunicazione");
        } finally {
          this.textInput.disabled = false;
          this.sendBtn.disabled = false;
        }
      },
      async startStream(message) {
        if (!message || message.trim() === "")
          return;
        const params = new URLSearchParams({
          message,
          team: this.teamSlugLocal,
          uuid: this.uuid || "",
          locale: this.locale,
          ts: String(Date.now())
        });
        if (this.threadId)
          params.set("thread_id", this.threadId);
        const webComponentOrigin = window.__ENJOY_HEN_ORIGIN__ || window.location.origin;
        const endpoint = `/api/chatbot/neuron-website-stream?${params.toString()}`;
        try {
          const thinkingBubble = document.getElementById("thinkingBubble");
          if (thinkingBubble) {
            thinkingBubble.classList.remove("hidden");
          }
          const eventSource = new EventSource(`${webComponentOrigin}${endpoint}`);
          let collected = "";
          let firstToken = true;
          this.setFeedback("Avatar sta rispondere...");
          eventSource.addEventListener("message", (e) => {
            try {
              const data = JSON.parse(e.data);
              if (data.token) {
                if (firstToken) {
                  firstToken = false;
                  if (thinkingBubble) {
                    thinkingBubble.classList.add("hidden");
                  }
                }
                try {
                  const tok = JSON.parse(data.token);
                  if (tok && tok.thread_id) {
                    this.threadId = tok.thread_id;
                    return;
                  }
                } catch {
                }
                collected += data.token;
              }
            } catch (err) {
              console.warn("Parse error:", err);
            }
          });
          eventSource.addEventListener("done", () => {
            try {
              eventSource.close();
            } catch {
            }
            if (thinkingBubble) {
              thinkingBubble.classList.add("hidden");
            }
            const text = this.stripHtml(collected).trim();
            if (text) {
              this.heygenSendRepeat(text);
            }
            this.setFeedback("");
          });
          eventSource.addEventListener("error", () => {
            try {
              eventSource.close();
            } catch {
            }
            if (thinkingBubble) {
              thinkingBubble.classList.add("hidden");
            }
            this.setFeedback("❌ Errore di connessione");
          });
        } catch (e) {
          console.error("Stream error:", e);
          this.setFeedback("❌ Errore dello stream");
        }
      },
      async ensureHeyGenSession() {
        var _a, _b, _c;
        if (this.heygen.started || this.heygen.connecting)
          return;
        this.heygen.connecting = true;
        try {
          if (!this.HEYGEN_CONFIG.apiKey || !this.HEYGEN_CONFIG.serverUrl) {
            this.setStatus("Config mancante");
            throw new Error("HEYGEN config missing");
          }
          this.setStatus("Richiesta token...");
          const tokRes = await fetch(
            `${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.create_token`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-Api-Key": this.HEYGEN_CONFIG.apiKey
              }
            }
          );
          const tokJson = await tokRes.json();
          this.heygen.sessionToken = (_a = tokJson == null ? void 0 : tokJson.data) == null ? void 0 : _a.token;
          if (!this.heygen.sessionToken)
            throw new Error("No session token");
          this.setStatus("Token OK");
          const body = {
            quality: "high",
            version: "v2",
            video_encoding: "H264"
          };
          if (this.heygenAvatar)
            body.avatar_name = this.heygenAvatar;
          if (this.heygenVoice)
            body.voice = { voice_id: this.heygenVoice, rate: 1 };
          const newRes = await fetch(
            `${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.new`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${this.heygen.sessionToken}`
              },
              body: JSON.stringify(body)
            }
          );
          const newJson = await newRes.json();
          this.heygen.sessionInfo = newJson == null ? void 0 : newJson.data;
          if (!((_b = this.heygen.sessionInfo) == null ? void 0 : _b.session_id))
            throw new Error("No session info");
          this.setStatus("Connessione video...");
          if (!window.LivekitClient) {
            await this.loadLiveKit();
          }
          this.heygen.room = new window.LivekitClient.Room({
            adaptiveStream: false,
            dynacast: true,
            videoCaptureDefaults: {
              resolution: window.LivekitClient.VideoPresets.h720.resolution
            }
          });
          this.heygen.mediaStream = new MediaStream();
          this.heygen.room.on(
            window.LivekitClient.RoomEvent.TrackSubscribed,
            async (track2) => {
              try {
                if (track2.kind === "video") {
                  this.heygen.mediaStream.addTrack(track2.mediaStreamTrack);
                  if (this.heygenVideo) {
                    this.heygenVideo.srcObject = this.heygen.mediaStream;
                    await this.heygenVideo.play().catch(() => {
                    });
                  }
                  this.setStatus("Video connesso");
                }
                if (track2.kind === "audio") {
                  this.heygen.mediaStream.addTrack(track2.mediaStreamTrack);
                  this.setStatus("Audio connesso");
                }
              } catch (e) {
                console.warn("Track subscription error:", e);
              }
            }
          );
          this.heygen.room.on(
            window.LivekitClient.RoomEvent.TrackUnsubscribed,
            (track2) => {
              const mt = track2.mediaStreamTrack;
              if (mt)
                this.heygen.mediaStream.removeTrack(mt);
            }
          );
          await this.heygen.room.prepareConnection(
            this.heygen.sessionInfo.url,
            this.heygen.sessionInfo.access_token
          );
          await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.start`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${this.heygen.sessionToken}`
            },
            body: JSON.stringify({ session_id: this.heygen.sessionInfo.session_id })
          });
          await this.heygen.room.connect(
            this.heygen.sessionInfo.url,
            this.heygen.sessionInfo.access_token
          );
          if (((_c = this.heygenVideo) == null ? void 0 : _c.srcObject) && this.heygenVideo.paused) {
            await this.heygenVideo.play();
          }
          this.heygen.started = true;
          this.setStatus("Connesso");
          this.loadingOverlay.classList.add("hidden");
        } catch (e) {
          console.error("HeyGen session error:", e);
          this.setStatus("Errore connessione");
        } finally {
          this.heygen.connecting = false;
        }
      },
      async heygenSendRepeat(text) {
        var _a;
        try {
          await this.ensureHeyGenSession();
          if (!((_a = this.heygen.sessionInfo) == null ? void 0 : _a.session_id))
            return;
          await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.task`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${this.heygen.sessionToken}`
            },
            body: JSON.stringify({
              session_id: this.heygen.sessionInfo.session_id,
              text,
              task_type: "repeat"
            })
          });
        } catch (e) {
          console.error("HeyGen repeat failed:", e);
        }
      },
      async cleanup() {
        var _a;
        try {
          if ((_a = this.heygen.sessionInfo) == null ? void 0 : _a.session_id) {
            await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.stop`, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${this.heygen.sessionToken}`
              },
              body: JSON.stringify({
                session_id: this.heygen.sessionInfo.session_id
              })
            });
          }
        } catch {
        }
        try {
          if (this.heygen.room)
            this.heygen.room.disconnect();
        } catch {
        }
        if (this.heygenVideo) {
          try {
            this.heygenVideo.pause();
          } catch {
          }
          this.heygenVideo.srcObject = null;
        }
      },
      loadLiveKit() {
        return new Promise((resolve) => {
          if (window.LivekitClient)
            return resolve();
          const s = document.createElement("script");
          s.src = "https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js";
          s.async = true;
          s.onload = () => resolve();
          s.onerror = () => resolve();
          document.head.appendChild(s);
        });
      },
      stripHtml(html) {
        const tmp = document.createElement("div");
        tmp.innerHTML = html;
        return (tmp.textContent || tmp.innerText || "").replace(/\s+/g, " ").trim();
      },
      setStatus(status) {
        if (this.videoAvatarStatus) {
          this.videoAvatarStatus.textContent = status;
        }
      },
      setFeedback(msg) {
        if (this.feedbackMsg) {
          this.feedbackMsg.textContent = msg;
        }
      }
    },
    setup() {
      const rootElRef = ref(null);
      const isWebComponent = true;
      return {
        isWebComponent,
        rootElRef
      };
    }
  });
  const _hoisted_1 = {
    key: 0,
    class: "px-4 py-4 border-b border-slate-700"
  };
  const _hoisted_2 = { class: "mx-auto w-full max-w-2xl flex items-center gap-3" };
  const _hoisted_3 = ["src"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("div", {
      ref: "rootEl",
      id: "enjoyHenRoot",
      class: normalizeClass(["flex flex-col", !_ctx.isWebComponent && "min-h-[100dvh] max-h-[100dvh]", "w-full bg-[#0f172a] overflow-hidden"])
    }, [
      !_ctx.isWebComponent ? (openBlock(), createElementBlock("div", _hoisted_1, [
        createBaseVNode("div", _hoisted_2, [
          createBaseVNode("img", {
            id: "teamLogo",
            src: _ctx.teamLogo,
            alt: "EnjoyHen",
            class: "w-10 h-10 rounded-full object-cover border border-slate-600"
          }, null, 8, _hoisted_3),
          _cache[0] || (_cache[0] = createBaseVNode("h1", { class: "font-sans text-2xl text-white" }, "EnjoyHen AI", -1))
        ])
      ])) : createCommentVNode("", true),
      _cache[1] || (_cache[1] = createBaseVNode("div", { class: "flex-1 flex items-center justify-center p-4 overflow-y-auto" }, [
        createBaseVNode("div", { class: "w-full max-w-2xl" }, [
          createBaseVNode("div", { class: "relative mb-6 rounded-lg overflow-hidden bg-black border border-slate-700" }, [
            createBaseVNode("video", {
              id: "heygenVideo",
              class: "w-full h-auto rounded-lg",
              autoplay: "",
              playsinline: "",
              controls: ""
            }),
            createBaseVNode("div", {
              id: "thinkingBubble",
              class: "hidden absolute top-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg px-4 py-2 shadow-lg border border-gray-300 z-10"
            }, [
              createBaseVNode("div", { class: "text-gray-700 text-sm font-medium" }, " 💭 Sto pensando... "),
              createBaseVNode("div", { class: "absolute bottom-0 left-1/2 transform translate-y-full -translate-x-1/2" }, [
                createBaseVNode("div", { class: "w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white" })
              ])
            ]),
            createBaseVNode("div", {
              id: "videoAvatarStatus",
              class: "absolute top-4 right-4 px-3 py-1 bg-slate-900/80 backdrop-blur text-slate-200 text-xs font-medium rounded-full border border-slate-700"
            }, " Inizializzazione... "),
            createBaseVNode("div", {
              id: "loadingOverlay",
              class: "absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center rounded-lg z-20"
            }, [
              createBaseVNode("div", { class: "flex flex-col items-center gap-4" }, [
                createBaseVNode("div", { class: "relative w-12 h-12" }, [
                  createBaseVNode("div", {
                    class: "absolute inset-0 bg-gradient-to-r from-indigo-600 to-emerald-600 rounded-full animate-spin",
                    style: { "border-radius": "50%", "-webkit-mask-image": "radial-gradient(circle 10px at center, transparent 100%, black 100%)", "mask-image": "radial-gradient(circle 10px at center, transparent 100%, black 100%)" }
                  }),
                  createBaseVNode("div", { class: "absolute inset-2 bg-black rounded-full flex items-center justify-center" }, [
                    createBaseVNode("div", { class: "w-2 h-2 bg-gradient-to-r from-indigo-400 to-emerald-400 rounded-full animate-pulse" })
                  ])
                ]),
                createBaseVNode("div", { class: "text-white text-sm font-medium" }, "Connessione in corso...")
              ])
            ]),
            createBaseVNode("div", {
              id: "startChatContainer",
              class: "hidden absolute inset-0 flex items-center justify-center z-25 rounded-lg bg-black/40 backdrop-blur-sm"
            }, [
              createBaseVNode("button", {
                id: "startChatBtn",
                class: "px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-colors text-lg shadow-lg hover:shadow-xl transform hover:scale-105"
              }, " 🎤 Inizia Chat ")
            ])
          ]),
          createBaseVNode("div", {
            id: "listeningBadge",
            class: "hidden px-3 py-2 bg-rose-600/90 text-white text-sm font-semibold rounded-md shadow animate-pulse text-center mb-4"
          }, " 🎤 Ascolto... ")
        ])
      ], -1)),
      _cache[2] || (_cache[2] = createStaticVNode('<div id="controlsBar" class="bottom-0 left-0 w-full border-t border-slate-700 bg-[#0f172a] z-20 pb-[env(safe-area-inset-bottom)]"><div class="px-3 py-3 sm:px-4 sm:py-4"><div class="mx-auto w-full max-w-2xl"><div class="flex flex-wrap w-full gap-2 items-center min-w-0"><input id="textInput" type="text" placeholder="Scrivi il tuo messaggio..." class="flex-1 min-w-0 px-3 py-3 bg-slate-700 text-white border border-slate-600 rounded-lg placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm sm:text-base"><button id="sendBtn" class="px-3 py-3 sm:px-4 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium"> 📤 Invia </button><button id="micBtn" class="px-3 py-3 sm:px-4 sm:py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium"> 🎤 Parla </button></div><div id="feedbackMsg" class="text-sm text-slate-400 text-center min-h-5 mt-2"></div></div></div></div>', 1))
    ], 2);
  }
  const EnjoyHen = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  const cssText = `/*!
 * Cropper.js v1.6.2
 * https://fengyuanchen.github.io/cropperjs
 *
 * Copyright 2015-present Chen Fengyuan
 * Released under the MIT license
 *
 * Date: 2024-04-21T07:43:02.731Z
 */

.cropper-container {
  direction: ltr;
  font-size: 0;
  line-height: 0;
  position: relative;
  touch-action: none;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
}

.cropper-container img {
    backface-visibility: hidden;
    display: block;
    height: 100%;
    image-orientation: 0deg;
    max-height: none !important;
    max-width: none !important;
    min-height: 0 !important;
    min-width: 0 !important;
    width: 100%;
  }

.cropper-wrap-box,
.cropper-canvas,
.cropper-drag-box,
.cropper-crop-box,
.cropper-modal {
  bottom: 0;
  left: 0;
  position: absolute;
  right: 0;
  top: 0;
}

.cropper-wrap-box,
.cropper-canvas {
  overflow: hidden;
}

.cropper-drag-box {
  background-color: #fff;
  opacity: 0;
}

.cropper-modal {
  background-color: #000;
  opacity: 0.5;
}

.cropper-view-box {
  display: block;
  height: 100%;
  outline: 1px solid #39f;
  outline-color: rgba(51, 153, 255, 0.75);
  overflow: hidden;
  width: 100%;
}

.cropper-dashed {
  border: 0 dashed #eee;
  display: block;
  opacity: 0.5;
  position: absolute;
}

.cropper-dashed.dashed-h {
    border-bottom-width: 1px;
    border-top-width: 1px;
    height: calc(100% / 3);
    left: 0;
    top: calc(100% / 3);
    width: 100%;
  }

.cropper-dashed.dashed-v {
    border-left-width: 1px;
    border-right-width: 1px;
    height: 100%;
    left: calc(100% / 3);
    top: 0;
    width: calc(100% / 3);
  }

.cropper-center {
  display: block;
  height: 0;
  left: 50%;
  opacity: 0.75;
  position: absolute;
  top: 50%;
  width: 0;
}

.cropper-center::before,
  .cropper-center::after {
    background-color: #eee;
    content: ' ';
    display: block;
    position: absolute;
  }

.cropper-center::before {
    height: 1px;
    left: -3px;
    top: 0;
    width: 7px;
  }

.cropper-center::after {
    height: 7px;
    left: 0;
    top: -3px;
    width: 1px;
  }

.cropper-face,
.cropper-line,
.cropper-point {
  display: block;
  height: 100%;
  opacity: 0.1;
  position: absolute;
  width: 100%;
}

.cropper-face {
  background-color: #fff;
  left: 0;
  top: 0;
}

.cropper-line {
  background-color: #39f;
}

.cropper-line.line-e {
    cursor: ew-resize;
    right: -3px;
    top: 0;
    width: 5px;
  }

.cropper-line.line-n {
    cursor: ns-resize;
    height: 5px;
    left: 0;
    top: -3px;
  }

.cropper-line.line-w {
    cursor: ew-resize;
    left: -3px;
    top: 0;
    width: 5px;
  }

.cropper-line.line-s {
    bottom: -3px;
    cursor: ns-resize;
    height: 5px;
    left: 0;
  }

.cropper-point {
  background-color: #39f;
  height: 5px;
  opacity: 0.75;
  width: 5px;
}

.cropper-point.point-e {
    cursor: ew-resize;
    margin-top: -3px;
    right: -3px;
    top: 50%;
  }

.cropper-point.point-n {
    cursor: ns-resize;
    left: 50%;
    margin-left: -3px;
    top: -3px;
  }

.cropper-point.point-w {
    cursor: ew-resize;
    left: -3px;
    margin-top: -3px;
    top: 50%;
  }

.cropper-point.point-s {
    bottom: -3px;
    cursor: s-resize;
    left: 50%;
    margin-left: -3px;
  }

.cropper-point.point-ne {
    cursor: nesw-resize;
    right: -3px;
    top: -3px;
  }

.cropper-point.point-nw {
    cursor: nwse-resize;
    left: -3px;
    top: -3px;
  }

.cropper-point.point-sw {
    bottom: -3px;
    cursor: nesw-resize;
    left: -3px;
  }

.cropper-point.point-se {
    bottom: -3px;
    cursor: nwse-resize;
    height: 20px;
    opacity: 1;
    right: -3px;
    width: 20px;
  }

@media (min-width: 768px) {

.cropper-point.point-se {
      height: 15px;
      width: 15px;
  }
    }

@media (min-width: 992px) {

.cropper-point.point-se {
      height: 10px;
      width: 10px;
  }
    }

@media (min-width: 1200px) {

.cropper-point.point-se {
      height: 5px;
      opacity: 0.75;
      width: 5px;
  }
    }

.cropper-point.point-se::before {
    background-color: #39f;
    bottom: -50%;
    content: ' ';
    display: block;
    height: 200%;
    opacity: 0;
    position: absolute;
    right: -50%;
    width: 200%;
  }

.cropper-invisible {
  opacity: 0;
}

.cropper-bg {
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC');
}

.cropper-hide {
  display: block;
  height: 0;
  position: absolute;
  width: 0;
}

.cropper-hidden {
  display: none !important;
}

.cropper-move {
  cursor: move;
}

.cropper-crop {
  cursor: crosshair;
}

.cropper-disabled .cropper-drag-box,
.cropper-disabled .cropper-face,
.cropper-disabled .cropper-line,
.cropper-disabled .cropper-point {
  cursor: not-allowed;
}

.curator-grid-container {
    /**
     * User input values.
     */
    --grid-layout-gap: 1.5rem;
    --grid-column-count: 1;
    --grid-item--min-width: 75px;

    /**
     * Calculated values.
     */
    --gap-count: calc(var(--grid-column-count) - 1);
    --total-gap-width: calc(var(--gap-count) * var(--grid-layout-gap));
    --grid-item--max-width: calc((100% - var(--total-gap-width)) / var(--grid-column-count));

    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(max(var(--grid-item--min-width), var(--grid-item--max-width)), 1fr));
    grid-gap: var(--grid-layout-gap);
}

@media (min-width: 768px) {
    .curator-grid-container {
        --grid-column-count: 2;
    }
}

@media (min-width: 1024px) {
    .curator-grid-container {
        --grid-column-count: 3;
    }
}

.checkered {
    background-color: rgba(var(--gray-200), 1);
    background-image: repeating-linear-gradient(45deg, rgba(var(--gray-300), 1) 25%, transparent 25%, transparent 75%, rgba(var(--gray-300), 1) 75%, rgba(var(--gray-300), 1)), repeating-linear-gradient(45deg, rgba(var(--gray-300), 1) 25%, rgba(var(--gray-200), 1) 25%, rgba(var(--gray-200), 1) 75%, rgba(var(--gray-300), 1) 75%, rgba(var(--gray-300), 1));

    background-position: 0 0, 10px 10px;
    background-size: 20px 20px;
}

.dark .checkered {
    background-color: rgba(var(--gray-800), 1);
    background-image: repeating-linear-gradient(45deg, rgba(var(--gray-700), 1) 25%, transparent 25%, transparent 75%, rgba(var(--gray-700), 1) 75%, rgba(var(--gray-700), 1)), repeating-linear-gradient(45deg, rgba(var(--gray-700), 1) 25%, rgba(var(--gray-800), 1) 25%, rgba(var(--gray-800), 1) 75%, rgba(var(--gray-700), 1) 75%, rgba(var(--gray-700), 1));
}

.fi-resource-media {
    .fi-forms-tabs-component-header {
    --tw-bg-opacity: 1;
    background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
  }
    .fi-forms-tabs-component-header:is(.dark *) {
    --tw-bg-opacity: 1;
    background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
  }

    .fi-ta {
        .fi-ta-record:has([type="checkbox"]:checked) {
      --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
      --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
      box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
    }
        .fi-ta-record:has([type="checkbox"]:checked) {
      --tw-ring-opacity: 1;
      --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
    }

        .fi-ta-record:not(:has([type="checkbox"])) .fi-ta-actions {
            margin-top: 56.25%;
        }

        .fi-ta-record:has(.curator-grid-column) {
      display: block;
    }

        .fi-ta-record:has(.curator-grid-column) {

            > div {
        flex-direction: column;
      }

            > div {
        padding: 0px;
      }

            > div {

                > div {
          padding: 0px;
        }
            }

            label {
        aspect-ratio: 16 / 9;
      }

            label {
        height: auto;
      }

            label {
        width: 100%;
      }

            label {
                z-index: 1;
            }

            [type="checkbox"]:not(:checked) {
        --tw-bg-opacity: 1;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity));
      }

            [type="checkbox"]:not(:checked):is(.dark *) {
        --tw-bg-opacity: 1;
        background-color: rgba(var(--gray-800), var(--tw-bg-opacity));
      }

            .fi-ta-actions {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
      }

            .fi-ta-actions {
        padding-bottom: 0.75rem;
      }
        }
    }
}

.curator-panel-sidebar {
    .fi-fo-component-ctn {
    gap: 1rem;
  }
    .fi-fo-component-ctn {

        label {
      font-size: 0.875rem;
      line-height: 1.25rem;
    }
    }

    .filepond--root {
        min-height: 300px;
    }
}

[wire\\:key*="open_curator_picker"],
[wire\\:key*="open_curation_panel"],
.curator-panel {
    > .fi-modal-window {
    padding: 0px !important;
  }
    > .fi-modal-window {

        > .fi-modal-header {
      border-bottom-width: 1px;
    }

        > .fi-modal-header {
      --tw-border-opacity: 1;
      border-color: rgba(var(--gray-300), var(--tw-border-opacity));
    }

        > .fi-modal-header {
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
    }

        > .fi-modal-header {
      padding-left: 1rem;
      padding-right: 1rem;
    }

        > .fi-modal-header:is(.dark *) {
      --tw-border-opacity: 1;
      border-color: rgba(var(--gray-800), var(--tw-border-opacity));
    }

        > .fi-modal-header {

            .fi-modal-heading {
        font-size: 1rem;
        line-height: 1.5rem;
      }

            .fi-modal-close-btn {
                margin-block-start: -0.75rem;

                &:hover,
                &:focus {
          background-color: transparent !important;
        }

                &:hover,
                &:focus {
          --tw-text-opacity: 1;
          color: rgba(var(--primary-500), var(--tw-text-opacity));
        }
            }
        }

        > .fi-modal-content {
            position: relative;
        }

        > .fi-modal-footer {
      padding-bottom: 0px;
    }

        .curator-picker-grid {
      display: grid;
    }

        .curator-picker-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

        .curator-picker-grid {
      gap: 1rem;
    }

        @media (min-width: 640px) {

      .curator-picker-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
    }

        @media (min-width: 768px) {

      .curator-picker-grid {
        grid-template-columns: repeat(6, minmax(0, 1fr));
      }
    }

        @media (min-width: 1280px) {

      .curator-picker-grid {
        grid-template-columns: repeat(8, minmax(0, 1fr));
      }
    }

        @media (min-width: 1536px) {

      .curator-picker-grid {
        grid-template-columns: repeat(10, minmax(0, 1fr));
      }
    }

        .filepond--panel {
      border-width: 2px;
    }

        .filepond--panel {
      border-style: dashed;
    }

        .filepond--panel {
      border-color: rgba(var(--gray-950), 0.05);
    }

        .filepond--panel {
      background-color: transparent;
    }

        .filepond--panel:is(.dark *) {
      border-color: rgb(255 255 255 / 0.2);
    }
    }
}

/*! tailwindcss v3.4.4 | MIT License | https://tailwindcss.com
 */

/*
1. Prevent padding and border from affecting element width. (https://github.com/mozdevs/cssremedy/issues/4)
2. Allow adding a border to an element by just adding a border-width. (https://github.com/tailwindcss/tailwindcss/pull/116)
*/

*,
::before,
::after {
  box-sizing: border-box; /* 1 */
  border-width: 0; /* 2 */
  border-style: solid; /* 2 */
  border-color: rgba(var(--gray-200), 1); /* 2 */
}

::before,
::after {
  --tw-content: '';
}

/*
1. Use a consistent sensible line-height in all browsers.
2. Prevent adjustments of font size after orientation changes in iOS.
3. Use a more readable tab size.
4. Use the user's configured \`sans\` font-family by default.
5. Use the user's configured \`sans\` font-feature-settings by default.
6. Use the user's configured \`sans\` font-variation-settings by default.
7. Disable tap highlights on iOS
*/

html,
:host {
  line-height: 1.5; /* 1 */
  -webkit-text-size-adjust: 100%; /* 2 */
  -moz-tab-size: 4; /* 3 */
  -o-tab-size: 4;
     tab-size: 4; /* 3 */
  font-family: Figtree, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; /* 4 */
  font-feature-settings: normal; /* 5 */
  font-variation-settings: normal; /* 6 */
  -webkit-tap-highlight-color: transparent; /* 7 */
}

/*
1. Remove the margin in all browsers.
2. Inherit line-height from \`html\` so users can set them as a class directly on the \`html\` element.
*/

body {
  margin: 0; /* 1 */
  line-height: inherit; /* 2 */
}

/*
1. Add the correct height in Firefox.
2. Correct the inheritance of border color in Firefox. (https://bugzilla.mozilla.org/show_bug.cgi?id=190655)
3. Ensure horizontal rules are visible by default.
*/

hr {
  height: 0; /* 1 */
  color: inherit; /* 2 */
  border-top-width: 1px; /* 3 */
}

/*
Add the correct text decoration in Chrome, Edge, and Safari.
*/

abbr:where([title]) {
  -webkit-text-decoration: underline dotted;
          text-decoration: underline dotted;
}

/*
Remove the default font size and weight for headings.
*/

h1,
h2,
h3,
h4,
h5,
h6 {
  font-size: inherit;
  font-weight: inherit;
}

/*
Reset links to optimize for opt-in styling instead of opt-out.
*/

a {
  color: inherit;
  text-decoration: inherit;
}

/*
Add the correct font weight in Edge and Safari.
*/

b,
strong {
  font-weight: bolder;
}

/*
1. Use the user's configured \`mono\` font-family by default.
2. Use the user's configured \`mono\` font-feature-settings by default.
3. Use the user's configured \`mono\` font-variation-settings by default.
4. Correct the odd \`em\` font sizing in all browsers.
*/

code,
kbd,
samp,
pre {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; /* 1 */
  font-feature-settings: normal; /* 2 */
  font-variation-settings: normal; /* 3 */
  font-size: 1em; /* 4 */
}

/*
Add the correct font size in all browsers.
*/

small {
  font-size: 80%;
}

/*
Prevent \`sub\` and \`sup\` elements from affecting the line height in all browsers.
*/

sub,
sup {
  font-size: 75%;
  line-height: 0;
  position: relative;
  vertical-align: baseline;
}

sub {
  bottom: -0.25em;
}

sup {
  top: -0.5em;
}

/*
1. Remove text indentation from table contents in Chrome and Safari. (https://bugs.chromium.org/p/chromium/issues/detail?id=999088, https://bugs.webkit.org/show_bug.cgi?id=201297)
2. Correct table border color inheritance in all Chrome and Safari. (https://bugs.chromium.org/p/chromium/issues/detail?id=935729, https://bugs.webkit.org/show_bug.cgi?id=195016)
3. Remove gaps between table borders by default.
*/

table {
  text-indent: 0; /* 1 */
  border-color: inherit; /* 2 */
  border-collapse: collapse; /* 3 */
}

/*
1. Change the font styles in all browsers.
2. Remove the margin in Firefox and Safari.
3. Remove default padding in all browsers.
*/

button,
input,
optgroup,
select,
textarea {
  font-family: inherit; /* 1 */
  font-feature-settings: inherit; /* 1 */
  font-variation-settings: inherit; /* 1 */
  font-size: 100%; /* 1 */
  font-weight: inherit; /* 1 */
  line-height: inherit; /* 1 */
  letter-spacing: inherit; /* 1 */
  color: inherit; /* 1 */
  margin: 0; /* 2 */
  padding: 0; /* 3 */
}

/*
Remove the inheritance of text transform in Edge and Firefox.
*/

button,
select {
  text-transform: none;
}

/*
1. Correct the inability to style clickable types in iOS and Safari.
2. Remove default button styles.
*/

button,
input:where([type='button']),
input:where([type='reset']),
input:where([type='submit']) {
  -webkit-appearance: button; /* 1 */
  background-color: transparent; /* 2 */
  background-image: none; /* 2 */
}

/*
Use the modern Firefox focus style for all focusable elements.
*/

:-moz-focusring {
  outline: auto;
}

/*
Remove the additional \`:invalid\` styles in Firefox. (https://github.com/mozilla/gecko-dev/blob/2f9eacd9d3d995c937b4251a5557d95d494c9be1/layout/style/res/forms.css#L728-L737)
*/

:-moz-ui-invalid {
  box-shadow: none;
}

/*
Add the correct vertical alignment in Chrome and Firefox.
*/

progress {
  vertical-align: baseline;
}

/*
Correct the cursor style of increment and decrement buttons in Safari.
*/

::-webkit-inner-spin-button,
::-webkit-outer-spin-button {
  height: auto;
}

/*
1. Correct the odd appearance in Chrome and Safari.
2. Correct the outline style in Safari.
*/

[type='search'] {
  -webkit-appearance: textfield; /* 1 */
  outline-offset: -2px; /* 2 */
}

/*
Remove the inner padding in Chrome and Safari on macOS.
*/

::-webkit-search-decoration {
  -webkit-appearance: none;
}

/*
1. Correct the inability to style clickable types in iOS and Safari.
2. Change font properties to \`inherit\` in Safari.
*/

::-webkit-file-upload-button {
  -webkit-appearance: button; /* 1 */
  font: inherit; /* 2 */
}

/*
Add the correct display in Chrome and Safari.
*/

summary {
  display: list-item;
}

/*
Removes the default spacing and border for appropriate elements.
*/

blockquote,
dl,
dd,
h1,
h2,
h3,
h4,
h5,
h6,
hr,
figure,
p,
pre {
  margin: 0;
}

fieldset {
  margin: 0;
  padding: 0;
}

legend {
  padding: 0;
}

ol,
ul,
menu {
  list-style: none;
  margin: 0;
  padding: 0;
}

/*
Reset default styling for dialogs.
*/

dialog {
  padding: 0;
}

/*
Prevent resizing textareas horizontally by default.
*/

textarea {
  resize: vertical;
}

/*
1. Reset the default placeholder opacity in Firefox. (https://github.com/tailwindlabs/tailwindcss/issues/3300)
2. Set the default placeholder color to the user's configured gray 400 color.
*/

input::-moz-placeholder, textarea::-moz-placeholder {
  opacity: 1; /* 1 */
  color: rgba(var(--gray-400), 1); /* 2 */
}

input::placeholder,
textarea::placeholder {
  opacity: 1; /* 1 */
  color: rgba(var(--gray-400), 1); /* 2 */
}

/*
Set the default cursor for buttons.
*/

button,
[role="button"] {
  cursor: pointer;
}

/*
Make sure disabled buttons don't get the pointer cursor.
*/

:disabled {
  cursor: default;
}

/*
1. Make replaced elements \`display: block\` by default. (https://github.com/mozdevs/cssremedy/issues/14)
2. Add \`vertical-align: middle\` to align replaced elements more sensibly by default. (https://github.com/jensimmons/cssremedy/issues/14#issuecomment-634934210)
   This can trigger a poorly considered lint error in some tools but is included by design.
*/

img,
svg,
video,
canvas,
audio,
iframe,
embed,
object {
  display: block; /* 1 */
  vertical-align: middle; /* 2 */
}

/*
Constrain images and videos to the parent width and preserve their intrinsic aspect ratio. (https://github.com/mozdevs/cssremedy/issues/14)
*/

img,
video {
  max-width: 100%;
  height: auto;
}

/* Make elements with the HTML hidden attribute stay hidden by default */

[hidden] {
  display: none;
}

[type='text'],input:where(:not([type])),[type='email'],[type='url'],[type='password'],[type='number'],[type='date'],[type='datetime-local'],[type='month'],[type='search'],[type='tel'],[type='time'],[type='week'],[multiple],textarea,select {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  border-radius: 0px;
  padding-top: 0.5rem;
  padding-right: 0.75rem;
  padding-bottom: 0.5rem;
  padding-left: 0.75rem;
  font-size: 1rem;
  line-height: 1.5rem;
  --tw-shadow: 0 0 #0000;
}

[type='text']:focus, input:where(:not([type])):focus, [type='email']:focus, [type='url']:focus, [type='password']:focus, [type='number']:focus, [type='date']:focus, [type='datetime-local']:focus, [type='month']:focus, [type='search']:focus, [type='tel']:focus, [type='time']:focus, [type='week']:focus, [multiple]:focus, textarea:focus, select:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 0px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
  border-color: #2563eb;
}

input::-moz-placeholder, textarea::-moz-placeholder {
  color: rgba(var(--gray-500), var(--tw-text-opacity, 1));
  opacity: 1;
}

input::placeholder,textarea::placeholder {
  color: rgba(var(--gray-500), var(--tw-text-opacity, 1));
  opacity: 1;
}

::-webkit-datetime-edit-fields-wrapper {
  padding: 0;
}

::-webkit-date-and-time-value {
  min-height: 1.5em;
  text-align: inherit;
}

::-webkit-datetime-edit {
  display: inline-flex;
}

::-webkit-datetime-edit,::-webkit-datetime-edit-year-field,::-webkit-datetime-edit-month-field,::-webkit-datetime-edit-day-field,::-webkit-datetime-edit-hour-field,::-webkit-datetime-edit-minute-field,::-webkit-datetime-edit-second-field,::-webkit-datetime-edit-millisecond-field,::-webkit-datetime-edit-meridiem-field {
  padding-top: 0;
  padding-bottom: 0;
}

select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='rgba(var(--gray-500)%2c var(--tw-stroke-opacity%2c 1))' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
}

[multiple],[size]:where(select:not([size="1"])) {
  background-image: initial;
  background-position: initial;
  background-repeat: unset;
  background-size: initial;
  padding-right: 0.75rem;
  -webkit-print-color-adjust: unset;
          print-color-adjust: unset;
}

[type='checkbox'],[type='radio'] {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  padding: 0;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
  display: inline-block;
  vertical-align: middle;
  background-origin: border-box;
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
  flex-shrink: 0;
  height: 1rem;
  width: 1rem;
  color: #2563eb;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  --tw-shadow: 0 0 #0000;
}

[type='checkbox'] {
  border-radius: 0px;
}

[type='radio'] {
  border-radius: 100%;
}

[type='checkbox']:focus,[type='radio']:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 2px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
}

[type='checkbox']:checked,[type='radio']:checked {
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

[type='checkbox']:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  [type='checkbox']:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='radio']:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  [type='radio']:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='checkbox']:checked:hover,[type='checkbox']:checked:focus,[type='radio']:checked:hover,[type='radio']:checked:focus {
  border-color: transparent;
  background-color: currentColor;
}

[type='checkbox']:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

@media (forced-colors: active)  {

  [type='checkbox']:indeterminate {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='checkbox']:indeterminate:hover,[type='checkbox']:indeterminate:focus {
  border-color: transparent;
  background-color: currentColor;
}

[type='file'] {
  background: unset;
  border-color: inherit;
  border-width: 0;
  border-radius: 0;
  padding: 0;
  font-size: unset;
  line-height: inherit;
}

[type='file']:focus {
  outline: 1px solid ButtonText;
  outline: 1px auto -webkit-focus-ring-color;
}

[type='text'],input:where(:not([type])),[type='email'],[type='url'],[type='password'],[type='number'],[type='date'],[type='datetime-local'],[type='month'],[type='search'],[type='tel'],[type='time'],[type='week'],[multiple],textarea,select {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  border-radius: 0px;
  padding-top: 0.5rem;
  padding-right: 0.75rem;
  padding-bottom: 0.5rem;
  padding-left: 0.75rem;
  font-size: 1rem;
  line-height: 1.5rem;
  --tw-shadow: 0 0 #0000;
}

[type='text']:focus, input:where(:not([type])):focus, [type='email']:focus, [type='url']:focus, [type='password']:focus, [type='number']:focus, [type='date']:focus, [type='datetime-local']:focus, [type='month']:focus, [type='search']:focus, [type='tel']:focus, [type='time']:focus, [type='week']:focus, [multiple]:focus, textarea:focus, select:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 0px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
  border-color: #2563eb;
}

input::-moz-placeholder, textarea::-moz-placeholder {
  color: rgba(var(--gray-500), var(--tw-text-opacity, 1));
  opacity: 1;
}

input::placeholder,textarea::placeholder {
  color: rgba(var(--gray-500), var(--tw-text-opacity, 1));
  opacity: 1;
}

::-webkit-datetime-edit-fields-wrapper {
  padding: 0;
}

::-webkit-date-and-time-value {
  min-height: 1.5em;
  text-align: inherit;
}

::-webkit-datetime-edit {
  display: inline-flex;
}

::-webkit-datetime-edit,::-webkit-datetime-edit-year-field,::-webkit-datetime-edit-month-field,::-webkit-datetime-edit-day-field,::-webkit-datetime-edit-hour-field,::-webkit-datetime-edit-minute-field,::-webkit-datetime-edit-second-field,::-webkit-datetime-edit-millisecond-field,::-webkit-datetime-edit-meridiem-field {
  padding-top: 0;
  padding-bottom: 0;
}

select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='rgba(var(--gray-500)%2c var(--tw-stroke-opacity%2c 1))' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
}

[multiple],[size]:where(select:not([size="1"])) {
  background-image: initial;
  background-position: initial;
  background-repeat: unset;
  background-size: initial;
  padding-right: 0.75rem;
  -webkit-print-color-adjust: unset;
          print-color-adjust: unset;
}

[type='checkbox'],[type='radio'] {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  padding: 0;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
  display: inline-block;
  vertical-align: middle;
  background-origin: border-box;
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
  flex-shrink: 0;
  height: 1rem;
  width: 1rem;
  color: #2563eb;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  --tw-shadow: 0 0 #0000;
}

[type='checkbox'] {
  border-radius: 0px;
}

[type='radio'] {
  border-radius: 100%;
}

[type='checkbox']:focus,[type='radio']:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 2px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
}

[type='checkbox']:checked,[type='radio']:checked {
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

[type='checkbox']:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  [type='checkbox']:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='radio']:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  [type='radio']:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='checkbox']:checked:hover,[type='checkbox']:checked:focus,[type='radio']:checked:hover,[type='radio']:checked:focus {
  border-color: transparent;
  background-color: currentColor;
}

[type='checkbox']:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

@media (forced-colors: active)  {

  [type='checkbox']:indeterminate {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

[type='checkbox']:indeterminate:hover,[type='checkbox']:indeterminate:focus {
  border-color: transparent;
  background-color: currentColor;
}

[type='file'] {
  background: unset;
  border-color: inherit;
  border-width: 0;
  border-radius: 0;
  padding: 0;
  font-size: unset;
  line-height: inherit;
}

[type='file']:focus {
  outline: 1px solid ButtonText;
  outline: 1px auto -webkit-focus-ring-color;
}

*, ::before, ::after {
  --tw-border-spacing-x: 0;
  --tw-border-spacing-y: 0;
  --tw-translate-x: 0;
  --tw-translate-y: 0;
  --tw-rotate: 0;
  --tw-skew-x: 0;
  --tw-skew-y: 0;
  --tw-scale-x: 1;
  --tw-scale-y: 1;
  --tw-pan-x:  ;
  --tw-pan-y:  ;
  --tw-pinch-zoom:  ;
  --tw-scroll-snap-strictness: proximity;
  --tw-gradient-from-position:  ;
  --tw-gradient-via-position:  ;
  --tw-gradient-to-position:  ;
  --tw-ordinal:  ;
  --tw-slashed-zero:  ;
  --tw-numeric-figure:  ;
  --tw-numeric-spacing:  ;
  --tw-numeric-fraction:  ;
  --tw-ring-inset:  ;
  --tw-ring-offset-width: 0px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: rgb(59 130 246 / 0.5);
  --tw-ring-offset-shadow: 0 0 #0000;
  --tw-ring-shadow: 0 0 #0000;
  --tw-shadow: 0 0 #0000;
  --tw-shadow-colored: 0 0 #0000;
  --tw-blur:  ;
  --tw-brightness:  ;
  --tw-contrast:  ;
  --tw-grayscale:  ;
  --tw-hue-rotate:  ;
  --tw-invert:  ;
  --tw-saturate:  ;
  --tw-sepia:  ;
  --tw-drop-shadow:  ;
  --tw-backdrop-blur:  ;
  --tw-backdrop-brightness:  ;
  --tw-backdrop-contrast:  ;
  --tw-backdrop-grayscale:  ;
  --tw-backdrop-hue-rotate:  ;
  --tw-backdrop-invert:  ;
  --tw-backdrop-opacity:  ;
  --tw-backdrop-saturate:  ;
  --tw-backdrop-sepia:  ;
  --tw-contain-size:  ;
  --tw-contain-layout:  ;
  --tw-contain-paint:  ;
  --tw-contain-style:  ;
}

::backdrop {
  --tw-border-spacing-x: 0;
  --tw-border-spacing-y: 0;
  --tw-translate-x: 0;
  --tw-translate-y: 0;
  --tw-rotate: 0;
  --tw-skew-x: 0;
  --tw-skew-y: 0;
  --tw-scale-x: 1;
  --tw-scale-y: 1;
  --tw-pan-x:  ;
  --tw-pan-y:  ;
  --tw-pinch-zoom:  ;
  --tw-scroll-snap-strictness: proximity;
  --tw-gradient-from-position:  ;
  --tw-gradient-via-position:  ;
  --tw-gradient-to-position:  ;
  --tw-ordinal:  ;
  --tw-slashed-zero:  ;
  --tw-numeric-figure:  ;
  --tw-numeric-spacing:  ;
  --tw-numeric-fraction:  ;
  --tw-ring-inset:  ;
  --tw-ring-offset-width: 0px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: rgb(59 130 246 / 0.5);
  --tw-ring-offset-shadow: 0 0 #0000;
  --tw-ring-shadow: 0 0 #0000;
  --tw-shadow: 0 0 #0000;
  --tw-shadow-colored: 0 0 #0000;
  --tw-blur:  ;
  --tw-brightness:  ;
  --tw-contrast:  ;
  --tw-grayscale:  ;
  --tw-hue-rotate:  ;
  --tw-invert:  ;
  --tw-saturate:  ;
  --tw-sepia:  ;
  --tw-drop-shadow:  ;
  --tw-backdrop-blur:  ;
  --tw-backdrop-brightness:  ;
  --tw-backdrop-contrast:  ;
  --tw-backdrop-grayscale:  ;
  --tw-backdrop-hue-rotate:  ;
  --tw-backdrop-invert:  ;
  --tw-backdrop-opacity:  ;
  --tw-backdrop-saturate:  ;
  --tw-backdrop-sepia:  ;
  --tw-contain-size:  ;
  --tw-contain-layout:  ;
  --tw-contain-paint:  ;
  --tw-contain-style:  ;
}

.container {
  width: 100%;
}

@media (min-width: 640px) {

  .container {
    max-width: 640px;
  }
}

@media (min-width: 768px) {

  .container {
    max-width: 768px;
  }
}

@media (min-width: 1024px) {

  .container {
    max-width: 1024px;
  }
}

@media (min-width: 1280px) {

  .container {
    max-width: 1280px;
  }
}

@media (min-width: 1536px) {

  .container {
    max-width: 1536px;
  }
}

.form-checkbox,.form-radio {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  padding: 0;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
  display: inline-block;
  vertical-align: middle;
  background-origin: border-box;
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
  flex-shrink: 0;
  height: 1rem;
  width: 1rem;
  color: #2563eb;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  --tw-shadow: 0 0 #0000;
}

.form-checkbox {
  border-radius: 0px;
}

.form-checkbox:focus,.form-radio:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 2px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
}

.form-checkbox:checked,.form-radio:checked {
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

.form-checkbox:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  .form-checkbox:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

.form-checkbox:checked:hover,.form-checkbox:checked:focus,.form-radio:checked:hover,.form-radio:checked:focus {
  border-color: transparent;
  background-color: currentColor;
}

.form-checkbox:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

@media (forced-colors: active)  {

  .form-checkbox:indeterminate {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

.form-checkbox:indeterminate:hover,.form-checkbox:indeterminate:focus {
  border-color: transparent;
  background-color: currentColor;
}

.prose {
  color: var(--tw-prose-body);
  max-width: 65ch;
}

.prose :where(p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}

.prose :where([class~="lead"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-lead);
  font-size: 1.25em;
  line-height: 1.6;
  margin-top: 1.2em;
  margin-bottom: 1.2em;
}

.prose :where(a):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-links);
  text-decoration: underline;
  font-weight: 500;
}

.prose :where(strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-bold);
  font-weight: 600;
}

.prose :where(a strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(blockquote strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(thead th strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: decimal;
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  padding-inline-start: 1.625em;
}

.prose :where(ol[type="A"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: upper-alpha;
}

.prose :where(ol[type="a"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: lower-alpha;
}

.prose :where(ol[type="A" s]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: upper-alpha;
}

.prose :where(ol[type="a" s]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: lower-alpha;
}

.prose :where(ol[type="I"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: upper-roman;
}

.prose :where(ol[type="i"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: lower-roman;
}

.prose :where(ol[type="I" s]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: upper-roman;
}

.prose :where(ol[type="i" s]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: lower-roman;
}

.prose :where(ol[type="1"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: decimal;
}

.prose :where(ul):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  list-style-type: disc;
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  padding-inline-start: 1.625em;
}

.prose :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *))::marker {
  font-weight: 400;
  color: var(--tw-prose-counters);
}

.prose :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *))::marker {
  color: var(--tw-prose-bullets);
}

.prose :where(dt):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 600;
  margin-top: 1.25em;
}

.prose :where(hr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  border-color: var(--tw-prose-hr);
  border-top-width: 1px;
  margin-top: 3em;
  margin-bottom: 3em;
}

.prose :where(blockquote):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 500;
  font-style: italic;
  color: var(--tw-prose-quotes);
  border-inline-start-width: 0.25rem;
  border-inline-start-color: var(--tw-prose-quote-borders);
  quotes: "\\201C""\\201D""\\2018""\\2019";
  margin-top: 1.6em;
  margin-bottom: 1.6em;
  padding-inline-start: 1em;
}

.prose :where(blockquote p:first-of-type):not(:where([class~="not-prose"],[class~="not-prose"] *))::before {
  content: open-quote;
}

.prose :where(blockquote p:last-of-type):not(:where([class~="not-prose"],[class~="not-prose"] *))::after {
  content: close-quote;
}

.prose :where(h1):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 800;
  font-size: 2.25em;
  margin-top: 0;
  margin-bottom: 0.8888889em;
  line-height: 1.1111111;
}

.prose :where(h1 strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 900;
  color: inherit;
}

.prose :where(h2):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 700;
  font-size: 1.5em;
  margin-top: 2em;
  margin-bottom: 1em;
  line-height: 1.3333333;
}

.prose :where(h2 strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 800;
  color: inherit;
}

.prose :where(h3):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 600;
  font-size: 1.25em;
  margin-top: 1.6em;
  margin-bottom: 0.6em;
  line-height: 1.6;
}

.prose :where(h3 strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 700;
  color: inherit;
}

.prose :where(h4):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 600;
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  line-height: 1.5;
}

.prose :where(h4 strong):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 700;
  color: inherit;
}

.prose :where(img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose :where(picture):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  display: block;
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose :where(video):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose :where(kbd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-weight: 500;
  font-family: inherit;
  color: var(--tw-prose-kbd);
  box-shadow: 0 0 0 1px rgb(var(--tw-prose-kbd-shadows) / 10%), 0 3px 0 rgb(var(--tw-prose-kbd-shadows) / 10%);
  font-size: 0.875em;
  border-radius: 0.3125rem;
  padding-top: 0.1875em;
  padding-inline-end: 0.375em;
  padding-bottom: 0.1875em;
  padding-inline-start: 0.375em;
}

.prose :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-code);
  font-weight: 600;
  font-size: 0.875em;
}

.prose :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *))::before {
  content: "\`";
}

.prose :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *))::after {
  content: "\`";
}

.prose :where(a code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(h1 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(h2 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
  font-size: 0.875em;
}

.prose :where(h3 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
  font-size: 0.9em;
}

.prose :where(h4 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(blockquote code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(thead th code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: inherit;
}

.prose :where(pre):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-pre-code);
  background-color: var(--tw-prose-pre-bg);
  overflow-x: auto;
  font-weight: 400;
  font-size: 0.875em;
  line-height: 1.7142857;
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
  border-radius: 0.375rem;
  padding-top: 0.8571429em;
  padding-inline-end: 1.1428571em;
  padding-bottom: 0.8571429em;
  padding-inline-start: 1.1428571em;
}

.prose :where(pre code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  background-color: transparent;
  border-width: 0;
  border-radius: 0;
  padding: 0;
  font-weight: inherit;
  color: inherit;
  font-size: inherit;
  font-family: inherit;
  line-height: inherit;
}

.prose :where(pre code):not(:where([class~="not-prose"],[class~="not-prose"] *))::before {
  content: none;
}

.prose :where(pre code):not(:where([class~="not-prose"],[class~="not-prose"] *))::after {
  content: none;
}

.prose :where(table):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  width: 100%;
  table-layout: auto;
  text-align: start;
  margin-top: 2em;
  margin-bottom: 2em;
  font-size: 0.875em;
  line-height: 1.7142857;
}

.prose :where(thead):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  border-bottom-width: 1px;
  border-bottom-color: var(--tw-prose-th-borders);
}

.prose :where(thead th):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-headings);
  font-weight: 600;
  vertical-align: bottom;
  padding-inline-end: 0.5714286em;
  padding-bottom: 0.5714286em;
  padding-inline-start: 0.5714286em;
}

.prose :where(tbody tr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  border-bottom-width: 1px;
  border-bottom-color: var(--tw-prose-td-borders);
}

.prose :where(tbody tr:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  border-bottom-width: 0;
}

.prose :where(tbody td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  vertical-align: baseline;
}

.prose :where(tfoot):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  border-top-width: 1px;
  border-top-color: var(--tw-prose-th-borders);
}

.prose :where(tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  vertical-align: top;
}

.prose :where(figure > *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose :where(figcaption):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  color: var(--tw-prose-captions);
  font-size: 0.875em;
  line-height: 1.4285714;
  margin-top: 0.8571429em;
}

.prose {
  --tw-prose-body: #374151;
  --tw-prose-headings: #111827;
  --tw-prose-lead: #4b5563;
  --tw-prose-links: #111827;
  --tw-prose-bold: #111827;
  --tw-prose-counters: #6b7280;
  --tw-prose-bullets: #d1d5db;
  --tw-prose-hr: #e5e7eb;
  --tw-prose-quotes: #111827;
  --tw-prose-quote-borders: #e5e7eb;
  --tw-prose-captions: #6b7280;
  --tw-prose-kbd: #111827;
  --tw-prose-kbd-shadows: 17 24 39;
  --tw-prose-code: #111827;
  --tw-prose-pre-code: #e5e7eb;
  --tw-prose-pre-bg: #1f2937;
  --tw-prose-th-borders: #d1d5db;
  --tw-prose-td-borders: #e5e7eb;
  --tw-prose-invert-body: #d1d5db;
  --tw-prose-invert-headings: #fff;
  --tw-prose-invert-lead: #9ca3af;
  --tw-prose-invert-links: #fff;
  --tw-prose-invert-bold: #fff;
  --tw-prose-invert-counters: #9ca3af;
  --tw-prose-invert-bullets: #4b5563;
  --tw-prose-invert-hr: #374151;
  --tw-prose-invert-quotes: #f3f4f6;
  --tw-prose-invert-quote-borders: #374151;
  --tw-prose-invert-captions: #9ca3af;
  --tw-prose-invert-kbd: #fff;
  --tw-prose-invert-kbd-shadows: 255 255 255;
  --tw-prose-invert-code: #fff;
  --tw-prose-invert-pre-code: #d1d5db;
  --tw-prose-invert-pre-bg: rgb(0 0 0 / 50%);
  --tw-prose-invert-th-borders: #4b5563;
  --tw-prose-invert-td-borders: #374151;
  font-size: 1rem;
  line-height: 1.75;
}

.prose :where(picture > img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose :where(li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.prose :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.375em;
}

.prose :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.375em;
}

.prose :where(.prose > ul > li p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.75em;
  margin-bottom: 0.75em;
}

.prose :where(.prose > ul > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
}

.prose :where(.prose > ul > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.25em;
}

.prose :where(.prose > ol > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
}

.prose :where(.prose > ol > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.25em;
}

.prose :where(ul ul, ul ol, ol ul, ol ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.75em;
  margin-bottom: 0.75em;
}

.prose :where(dl):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}

.prose :where(dd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5em;
  padding-inline-start: 1.625em;
}

.prose :where(hr + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose :where(h2 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose :where(h3 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose :where(h4 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose :where(thead th:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose :where(thead th:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose :where(tbody td, tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-top: 0.5714286em;
  padding-inline-end: 0.5714286em;
  padding-bottom: 0.5714286em;
  padding-inline-start: 0.5714286em;
}

.prose :where(tbody td:first-child, tfoot td:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose :where(tbody td:last-child, tfoot td:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose :where(figure):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose :where(.prose > :first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose :where(.prose > :last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 0;
}

.prose-sm {
  font-size: 0.875rem;
  line-height: 1.7142857;
}

.prose-sm :where(p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
  margin-bottom: 1.1428571em;
}

.prose-sm :where([class~="lead"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.2857143em;
  line-height: 1.5555556;
  margin-top: 0.8888889em;
  margin-bottom: 0.8888889em;
}

.prose-sm :where(blockquote):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
  margin-bottom: 1.3333333em;
  padding-inline-start: 1.1111111em;
}

.prose-sm :where(h1):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 2.1428571em;
  margin-top: 0;
  margin-bottom: 0.8em;
  line-height: 1.2;
}

.prose-sm :where(h2):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.4285714em;
  margin-top: 1.6em;
  margin-bottom: 0.8em;
  line-height: 1.4;
}

.prose-sm :where(h3):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.2857143em;
  margin-top: 1.5555556em;
  margin-bottom: 0.4444444em;
  line-height: 1.5555556;
}

.prose-sm :where(h4):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.4285714em;
  margin-bottom: 0.5714286em;
  line-height: 1.4285714;
}

.prose-sm :where(img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
}

.prose-sm :where(picture):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
}

.prose-sm :where(picture > img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-sm :where(video):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
}

.prose-sm :where(kbd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8571429em;
  border-radius: 0.3125rem;
  padding-top: 0.1428571em;
  padding-inline-end: 0.3571429em;
  padding-bottom: 0.1428571em;
  padding-inline-start: 0.3571429em;
}

.prose-sm :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8571429em;
}

.prose-sm :where(h2 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
}

.prose-sm :where(h3 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
}

.prose-sm :where(pre):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8571429em;
  line-height: 1.6666667;
  margin-top: 1.6666667em;
  margin-bottom: 1.6666667em;
  border-radius: 0.25rem;
  padding-top: 0.6666667em;
  padding-inline-end: 1em;
  padding-bottom: 0.6666667em;
  padding-inline-start: 1em;
}

.prose-sm :where(ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
  margin-bottom: 1.1428571em;
  padding-inline-start: 1.5714286em;
}

.prose-sm :where(ul):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
  margin-bottom: 1.1428571em;
  padding-inline-start: 1.5714286em;
}

.prose-sm :where(li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.2857143em;
  margin-bottom: 0.2857143em;
}

.prose-sm :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4285714em;
}

.prose-sm :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4285714em;
}

.prose-sm :where(.prose-sm > ul > li p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5714286em;
  margin-bottom: 0.5714286em;
}

.prose-sm :where(.prose-sm > ul > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
}

.prose-sm :where(.prose-sm > ul > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.1428571em;
}

.prose-sm :where(.prose-sm > ol > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
}

.prose-sm :where(.prose-sm > ol > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.1428571em;
}

.prose-sm :where(ul ul, ul ol, ol ul, ol ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5714286em;
  margin-bottom: 0.5714286em;
}

.prose-sm :where(dl):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
  margin-bottom: 1.1428571em;
}

.prose-sm :where(dt):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.1428571em;
}

.prose-sm :where(dd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.2857143em;
  padding-inline-start: 1.5714286em;
}

.prose-sm :where(hr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2.8571429em;
  margin-bottom: 2.8571429em;
}

.prose-sm :where(hr + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-sm :where(h2 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-sm :where(h3 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-sm :where(h4 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-sm :where(table):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8571429em;
  line-height: 1.5;
}

.prose-sm :where(thead th):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 1em;
  padding-bottom: 0.6666667em;
  padding-inline-start: 1em;
}

.prose-sm :where(thead th:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-sm :where(thead th:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-sm :where(tbody td, tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-top: 0.6666667em;
  padding-inline-end: 1em;
  padding-bottom: 0.6666667em;
  padding-inline-start: 1em;
}

.prose-sm :where(tbody td:first-child, tfoot td:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-sm :where(tbody td:last-child, tfoot td:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-sm :where(figure):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
}

.prose-sm :where(figure > *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-sm :where(figcaption):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8571429em;
  line-height: 1.3333333;
  margin-top: 0.6666667em;
}

.prose-sm :where(.prose-sm > :first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-sm :where(.prose-sm > :last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 0;
}

.prose-base {
  font-size: 1rem;
  line-height: 1.75;
}

.prose-base :where(p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}

.prose-base :where([class~="lead"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.25em;
  line-height: 1.6;
  margin-top: 1.2em;
  margin-bottom: 1.2em;
}

.prose-base :where(blockquote):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.6em;
  margin-bottom: 1.6em;
  padding-inline-start: 1em;
}

.prose-base :where(h1):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 2.25em;
  margin-top: 0;
  margin-bottom: 0.8888889em;
  line-height: 1.1111111;
}

.prose-base :where(h2):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.5em;
  margin-top: 2em;
  margin-bottom: 1em;
  line-height: 1.3333333;
}

.prose-base :where(h3):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.25em;
  margin-top: 1.6em;
  margin-bottom: 0.6em;
  line-height: 1.6;
}

.prose-base :where(h4):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  line-height: 1.5;
}

.prose-base :where(img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-base :where(picture):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-base :where(picture > img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-base :where(video):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-base :where(kbd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
  border-radius: 0.3125rem;
  padding-top: 0.1875em;
  padding-inline-end: 0.375em;
  padding-bottom: 0.1875em;
  padding-inline-start: 0.375em;
}

.prose-base :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
}

.prose-base :where(h2 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
}

.prose-base :where(h3 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
}

.prose-base :where(pre):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
  line-height: 1.7142857;
  margin-top: 1.7142857em;
  margin-bottom: 1.7142857em;
  border-radius: 0.375rem;
  padding-top: 0.8571429em;
  padding-inline-end: 1.1428571em;
  padding-bottom: 0.8571429em;
  padding-inline-start: 1.1428571em;
}

.prose-base :where(ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  padding-inline-start: 1.625em;
}

.prose-base :where(ul):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
  padding-inline-start: 1.625em;
}

.prose-base :where(li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.prose-base :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.375em;
}

.prose-base :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.375em;
}

.prose-base :where(.prose-base > ul > li p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.75em;
  margin-bottom: 0.75em;
}

.prose-base :where(.prose-base > ul > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
}

.prose-base :where(.prose-base > ul > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.25em;
}

.prose-base :where(.prose-base > ol > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
}

.prose-base :where(.prose-base > ol > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.25em;
}

.prose-base :where(ul ul, ul ol, ol ul, ol ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.75em;
  margin-bottom: 0.75em;
}

.prose-base :where(dl):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
  margin-bottom: 1.25em;
}

.prose-base :where(dt):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.25em;
}

.prose-base :where(dd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.5em;
  padding-inline-start: 1.625em;
}

.prose-base :where(hr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 3em;
  margin-bottom: 3em;
}

.prose-base :where(hr + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-base :where(h2 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-base :where(h3 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-base :where(h4 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-base :where(table):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
  line-height: 1.7142857;
}

.prose-base :where(thead th):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0.5714286em;
  padding-bottom: 0.5714286em;
  padding-inline-start: 0.5714286em;
}

.prose-base :where(thead th:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-base :where(thead th:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-base :where(tbody td, tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-top: 0.5714286em;
  padding-inline-end: 0.5714286em;
  padding-bottom: 0.5714286em;
  padding-inline-start: 0.5714286em;
}

.prose-base :where(tbody td:first-child, tfoot td:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-base :where(tbody td:last-child, tfoot td:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-base :where(figure):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-base :where(figure > *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-base :where(figcaption):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
  line-height: 1.4285714;
  margin-top: 0.8571429em;
}

.prose-base :where(.prose-base > :first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-base :where(.prose-base > :last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 0;
}

.prose-lg {
  font-size: 1.125rem;
  line-height: 1.7777778;
}

.prose-lg :where(p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
  margin-bottom: 1.3333333em;
}

.prose-lg :where([class~="lead"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.2222222em;
  line-height: 1.4545455;
  margin-top: 1.0909091em;
  margin-bottom: 1.0909091em;
}

.prose-lg :where(blockquote):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.6666667em;
  margin-bottom: 1.6666667em;
  padding-inline-start: 1em;
}

.prose-lg :where(h1):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 2.6666667em;
  margin-top: 0;
  margin-bottom: 0.8333333em;
  line-height: 1;
}

.prose-lg :where(h2):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.6666667em;
  margin-top: 1.8666667em;
  margin-bottom: 1.0666667em;
  line-height: 1.3333333;
}

.prose-lg :where(h3):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.3333333em;
  margin-top: 1.6666667em;
  margin-bottom: 0.6666667em;
  line-height: 1.5;
}

.prose-lg :where(h4):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7777778em;
  margin-bottom: 0.4444444em;
  line-height: 1.5555556;
}

.prose-lg :where(img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7777778em;
  margin-bottom: 1.7777778em;
}

.prose-lg :where(picture):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7777778em;
  margin-bottom: 1.7777778em;
}

.prose-lg :where(picture > img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-lg :where(video):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7777778em;
  margin-bottom: 1.7777778em;
}

.prose-lg :where(kbd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
  border-radius: 0.3125rem;
  padding-top: 0.2222222em;
  padding-inline-end: 0.4444444em;
  padding-bottom: 0.2222222em;
  padding-inline-start: 0.4444444em;
}

.prose-lg :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
}

.prose-lg :where(h2 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8666667em;
}

.prose-lg :where(h3 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.875em;
}

.prose-lg :where(pre):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
  line-height: 1.75;
  margin-top: 2em;
  margin-bottom: 2em;
  border-radius: 0.375rem;
  padding-top: 1em;
  padding-inline-end: 1.5em;
  padding-bottom: 1em;
  padding-inline-start: 1.5em;
}

.prose-lg :where(ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
  margin-bottom: 1.3333333em;
  padding-inline-start: 1.5555556em;
}

.prose-lg :where(ul):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
  margin-bottom: 1.3333333em;
  padding-inline-start: 1.5555556em;
}

.prose-lg :where(li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.6666667em;
  margin-bottom: 0.6666667em;
}

.prose-lg :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4444444em;
}

.prose-lg :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4444444em;
}

.prose-lg :where(.prose-lg > ul > li p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.8888889em;
  margin-bottom: 0.8888889em;
}

.prose-lg :where(.prose-lg > ul > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
}

.prose-lg :where(.prose-lg > ul > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.3333333em;
}

.prose-lg :where(.prose-lg > ol > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
}

.prose-lg :where(.prose-lg > ol > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.3333333em;
}

.prose-lg :where(ul ul, ul ol, ol ul, ol ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.8888889em;
  margin-bottom: 0.8888889em;
}

.prose-lg :where(dl):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
  margin-bottom: 1.3333333em;
}

.prose-lg :where(dt):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.3333333em;
}

.prose-lg :where(dd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.6666667em;
  padding-inline-start: 1.5555556em;
}

.prose-lg :where(hr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 3.1111111em;
  margin-bottom: 3.1111111em;
}

.prose-lg :where(hr + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-lg :where(h2 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-lg :where(h3 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-lg :where(h4 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-lg :where(table):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
  line-height: 1.5;
}

.prose-lg :where(thead th):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0.75em;
  padding-bottom: 0.75em;
  padding-inline-start: 0.75em;
}

.prose-lg :where(thead th:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-lg :where(thead th:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-lg :where(tbody td, tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-top: 0.75em;
  padding-inline-end: 0.75em;
  padding-bottom: 0.75em;
  padding-inline-start: 0.75em;
}

.prose-lg :where(tbody td:first-child, tfoot td:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-lg :where(tbody td:last-child, tfoot td:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-lg :where(figure):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.7777778em;
  margin-bottom: 1.7777778em;
}

.prose-lg :where(figure > *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-lg :where(figcaption):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8888889em;
  line-height: 1.5;
  margin-top: 1em;
}

.prose-lg :where(.prose-lg > :first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-lg :where(.prose-lg > :last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 0;
}

.prose-xl {
  font-size: 1.25rem;
  line-height: 1.8;
}

.prose-xl :where(p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
  margin-bottom: 1.2em;
}

.prose-xl :where([class~="lead"]):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.2em;
  line-height: 1.5;
  margin-top: 1em;
  margin-bottom: 1em;
}

.prose-xl :where(blockquote):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.6em;
  margin-bottom: 1.6em;
  padding-inline-start: 1.0666667em;
}

.prose-xl :where(h1):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 2.8em;
  margin-top: 0;
  margin-bottom: 0.8571429em;
  line-height: 1;
}

.prose-xl :where(h2):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.8em;
  margin-top: 1.5555556em;
  margin-bottom: 0.8888889em;
  line-height: 1.1111111;
}

.prose-xl :where(h3):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 1.5em;
  margin-top: 1.6em;
  margin-bottom: 0.6666667em;
  line-height: 1.3333333;
}

.prose-xl :where(h4):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.8em;
  margin-bottom: 0.6em;
  line-height: 1.6;
}

.prose-xl :where(img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-xl :where(picture):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-xl :where(picture > img):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-xl :where(video):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-xl :where(kbd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
  border-radius: 0.3125rem;
  padding-top: 0.25em;
  padding-inline-end: 0.4em;
  padding-bottom: 0.25em;
  padding-inline-start: 0.4em;
}

.prose-xl :where(code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
}

.prose-xl :where(h2 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.8611111em;
}

.prose-xl :where(h3 code):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
}

.prose-xl :where(pre):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
  line-height: 1.7777778;
  margin-top: 2em;
  margin-bottom: 2em;
  border-radius: 0.5rem;
  padding-top: 1.1111111em;
  padding-inline-end: 1.3333333em;
  padding-bottom: 1.1111111em;
  padding-inline-start: 1.3333333em;
}

.prose-xl :where(ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
  margin-bottom: 1.2em;
  padding-inline-start: 1.6em;
}

.prose-xl :where(ul):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
  margin-bottom: 1.2em;
  padding-inline-start: 1.6em;
}

.prose-xl :where(li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.6em;
  margin-bottom: 0.6em;
}

.prose-xl :where(ol > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4em;
}

.prose-xl :where(ul > li):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0.4em;
}

.prose-xl :where(.prose-xl > ul > li p):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.8em;
  margin-bottom: 0.8em;
}

.prose-xl :where(.prose-xl > ul > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
}

.prose-xl :where(.prose-xl > ul > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.2em;
}

.prose-xl :where(.prose-xl > ol > li > p:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
}

.prose-xl :where(.prose-xl > ol > li > p:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 1.2em;
}

.prose-xl :where(ul ul, ul ol, ol ul, ol ol):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.8em;
  margin-bottom: 0.8em;
}

.prose-xl :where(dl):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
  margin-bottom: 1.2em;
}

.prose-xl :where(dt):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 1.2em;
}

.prose-xl :where(dd):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0.6em;
  padding-inline-start: 1.6em;
}

.prose-xl :where(hr):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2.8em;
  margin-bottom: 2.8em;
}

.prose-xl :where(hr + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-xl :where(h2 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-xl :where(h3 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-xl :where(h4 + *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-xl :where(table):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
  line-height: 1.5555556;
}

.prose-xl :where(thead th):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0.6666667em;
  padding-bottom: 0.8888889em;
  padding-inline-start: 0.6666667em;
}

.prose-xl :where(thead th:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-xl :where(thead th:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-xl :where(tbody td, tfoot td):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-top: 0.8888889em;
  padding-inline-end: 0.6666667em;
  padding-bottom: 0.8888889em;
  padding-inline-start: 0.6666667em;
}

.prose-xl :where(tbody td:first-child, tfoot td:first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-start: 0;
}

.prose-xl :where(tbody td:last-child, tfoot td:last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  padding-inline-end: 0;
}

.prose-xl :where(figure):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 2em;
  margin-bottom: 2em;
}

.prose-xl :where(figure > *):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 0;
}

.prose-xl :where(figcaption):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  font-size: 0.9em;
  line-height: 1.5555556;
  margin-top: 1em;
}

.prose-xl :where(.prose-xl > :first-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-top: 0;
}

.prose-xl :where(.prose-xl > :last-child):not(:where([class~="not-prose"],[class~="not-prose"] *)) {
  margin-bottom: 0;
}

.form-checkbox,.form-radio {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  padding: 0;
  -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
  display: inline-block;
  vertical-align: middle;
  background-origin: border-box;
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
  flex-shrink: 0;
  height: 1rem;
  width: 1rem;
  color: #2563eb;
  background-color: #fff;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity, 1));
  border-width: 1px;
  --tw-shadow: 0 0 #0000;
}

.form-checkbox {
  border-radius: 0px;
}

.form-checkbox:focus,.form-radio:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  --tw-ring-inset: var(--tw-empty,/*!*/ /*!*/);
  --tw-ring-offset-width: 2px;
  --tw-ring-offset-color: #fff;
  --tw-ring-color: #2563eb;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
}

.form-checkbox:checked,.form-radio:checked {
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

.form-checkbox:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

@media (forced-colors: active)  {

  .form-checkbox:checked {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

.form-checkbox:checked:hover,.form-checkbox:checked:focus,.form-radio:checked:hover,.form-radio:checked:focus {
  border-color: transparent;
  background-color: currentColor;
}

.form-checkbox:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
  border-color: transparent;
  background-color: currentColor;
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

@media (forced-colors: active)  {

  .form-checkbox:indeterminate {
    -webkit-appearance: auto;
       -moz-appearance: auto;
            appearance: auto;
  }
}

.form-checkbox:indeterminate:hover,.form-checkbox:indeterminate:focus {
  border-color: transparent;
  background-color: currentColor;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

.pointer-events-none {
  pointer-events: none;
}

.pointer-events-auto {
  pointer-events: auto;
}

.visible {
  visibility: visible;
}

.invisible {
  visibility: hidden;
}

.collapse {
  visibility: collapse;
}

.static {
  position: static;
}

.fixed {
  position: fixed;
}

.absolute {
  position: absolute;
}

.relative {
  position: relative;
}

.sticky {
  position: sticky;
}

.inset-0 {
  inset: 0px;
}

.inset-2 {
  inset: 0.5rem;
}

.inset-4 {
  inset: 1rem;
}

.inset-x-0 {
  left: 0px;
  right: 0px;
}

.inset-x-4 {
  left: 1rem;
  right: 1rem;
}

.inset-y-0 {
  top: 0px;
  bottom: 0px;
}

.-bottom-1\\/2 {
  bottom: -50%;
}

.-top-1 {
  top: -0.25rem;
}

.-top-1\\/2 {
  top: -50%;
}

.-top-2 {
  top: -0.5rem;
}

.-top-3 {
  top: -0.75rem;
}

.bottom-0 {
  bottom: 0px;
}

.bottom-1\\/2 {
  bottom: 50%;
}

.bottom-4 {
  bottom: 1rem;
}

.end-0 {
  inset-inline-end: 0px;
}

.end-4 {
  inset-inline-end: 1rem;
}

.end-6 {
  inset-inline-end: 1.5rem;
}

.left-0 {
  left: 0px;
}

.left-1\\/2 {
  left: 50%;
}

.left-2 {
  left: 0.5rem;
}

.left-3 {
  left: 0.75rem;
}

.left-4 {
  left: 1rem;
}

.right-0 {
  right: 0px;
}

.right-2 {
  right: 0.5rem;
}

.right-3 {
  right: 0.75rem;
}

.right-4 {
  right: 1rem;
}

.right-5 {
  right: 1.25rem;
}

.start-0 {
  inset-inline-start: 0px;
}

.start-full {
  inset-inline-start: 100%;
}

.top-0 {
  top: 0px;
}

.top-1 {
  top: 0.25rem;
}

.top-1\\.5 {
  top: 0.375rem;
}

.top-1\\/2 {
  top: 50%;
}

.top-2 {
  top: 0.5rem;
}

.top-3 {
  top: 0.75rem;
}

.top-4 {
  top: 1rem;
}

.top-5 {
  top: 1.25rem;
}

.top-6 {
  top: 1.5rem;
}

.top-auto {
  top: auto;
}

.isolate {
  isolation: isolate;
}

.-z-50 {
  z-index: -50;
}

.z-0 {
  z-index: 0;
}

.z-10 {
  z-index: 10;
}

.z-20 {
  z-index: 20;
}

.z-30 {
  z-index: 30;
}

.z-40 {
  z-index: 40;
}

.z-50 {
  z-index: 50;
}

.z-\\[1\\] {
  z-index: 1;
}

.order-1 {
  order: 1;
}

.order-first {
  order: -9999;
}

.col-\\[--col-span-default\\] {
  grid-column: var(--col-span-default);
}

.col-span-1 {
  grid-column: span 1 / span 1;
}

.col-span-12 {
  grid-column: span 12 / span 12;
}

.col-span-2 {
  grid-column: span 2 / span 2;
}

.col-span-3 {
  grid-column: span 3 / span 3;
}

.col-span-full {
  grid-column: 1 / -1;
}

.col-start-2 {
  grid-column-start: 2;
}

.col-start-3 {
  grid-column-start: 3;
}

.col-start-\\[--col-start-default\\] {
  grid-column-start: var(--col-start-default);
}

.row-start-2 {
  grid-row-start: 2;
}

.-m-0 {
  margin: -0px;
}

.-m-0\\.5 {
  margin: -0.125rem;
}

.-m-1 {
  margin: -0.25rem;
}

.-m-1\\.5 {
  margin: -0.375rem;
}

.-m-16 {
  margin: -4rem;
}

.-m-2 {
  margin: -0.5rem;
}

.-m-2\\.5 {
  margin: -0.625rem;
}

.-m-3 {
  margin: -0.75rem;
}

.-m-3\\.5 {
  margin: -0.875rem;
}

.-m-4 {
  margin: -1rem;
}

.m-0 {
  margin: 0px;
}

.-mx-2 {
  margin-left: -0.5rem;
  margin-right: -0.5rem;
}

.-mx-4 {
  margin-left: -1rem;
  margin-right: -1rem;
}

.-mx-6 {
  margin-left: -1.5rem;
  margin-right: -1.5rem;
}

.-my-1 {
  margin-top: -0.25rem;
  margin-bottom: -0.25rem;
}

.mx-1 {
  margin-left: 0.25rem;
  margin-right: 0.25rem;
}

.mx-2 {
  margin-left: 0.5rem;
  margin-right: 0.5rem;
}

.mx-3 {
  margin-left: 0.75rem;
  margin-right: 0.75rem;
}

.mx-4 {
  margin-left: 1rem;
  margin-right: 1rem;
}

.mx-6 {
  margin-left: 1.5rem;
  margin-right: 1.5rem;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.my-16 {
  margin-top: 4rem;
  margin-bottom: 4rem;
}

.my-2 {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

.my-4 {
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.my-6 {
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
}

.my-8 {
  margin-top: 2rem;
  margin-bottom: 2rem;
}

.my-auto {
  margin-top: auto;
  margin-bottom: auto;
}

.\\!mt-0 {
  margin-top: 0px !important;
}

.-mb-1 {
  margin-bottom: -0.25rem;
}

.-mb-4 {
  margin-bottom: -1rem;
}

.-mb-6 {
  margin-bottom: -1.5rem;
}

.-me-2 {
  margin-inline-end: -0.5rem;
}

.-ml-1 {
  margin-left: -0.25rem;
}

.-ml-20 {
  margin-left: -5rem;
}

.-ml-px {
  margin-left: -1px;
}

.-mr-1 {
  margin-right: -0.25rem;
}

.-mr-2 {
  margin-right: -0.5rem;
}

.-ms-0 {
  margin-inline-start: -0px;
}

.-ms-0\\.5 {
  margin-inline-start: -0.125rem;
}

.-ms-1 {
  margin-inline-start: -0.25rem;
}

.-ms-2 {
  margin-inline-start: -0.5rem;
}

.-mt-16 {
  margin-top: -4rem;
}

.-mt-3 {
  margin-top: -0.75rem;
}

.-mt-4 {
  margin-top: -1rem;
}

.-mt-6 {
  margin-top: -1.5rem;
}

.-mt-64 {
  margin-top: -16rem;
}

.-mt-7 {
  margin-top: -1.75rem;
}

.-mt-px {
  margin-top: -1px;
}

.mb-0 {
  margin-bottom: 0px;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.mb-10 {
  margin-bottom: 2.5rem;
}

.mb-11 {
  margin-bottom: 2.75rem;
}

.mb-12 {
  margin-bottom: 3rem;
}

.mb-16 {
  margin-bottom: 4rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mb-20 {
  margin-bottom: 5rem;
}

.mb-3 {
  margin-bottom: 0.75rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.mb-40 {
  margin-bottom: 10rem;
}

.mb-5 {
  margin-bottom: 1.25rem;
}

.mb-6 {
  margin-bottom: 1.5rem;
}

.mb-8 {
  margin-bottom: 2rem;
}

.me-1 {
  margin-inline-end: 0.25rem;
}

.me-2 {
  margin-inline-end: 0.5rem;
}

.me-3 {
  margin-inline-end: 0.75rem;
}

.me-4 {
  margin-inline-end: 1rem;
}

.me-6 {
  margin-inline-end: 1.5rem;
}

.ml-1 {
  margin-left: 0.25rem;
}

.ml-2 {
  margin-left: 0.5rem;
}

.ml-3 {
  margin-left: 0.75rem;
}

.ml-4 {
  margin-left: 1rem;
}

.ml-\\[5\\%\\] {
  margin-left: 5%;
}

.ml-auto {
  margin-left: auto;
}

.mr-1 {
  margin-right: 0.25rem;
}

.mr-2 {
  margin-right: 0.5rem;
}

.mr-2\\.5 {
  margin-right: 0.625rem;
}

.mr-3 {
  margin-right: 0.75rem;
}

.ms-1 {
  margin-inline-start: 0.25rem;
}

.ms-2 {
  margin-inline-start: 0.5rem;
}

.ms-3 {
  margin-inline-start: 0.75rem;
}

.ms-4 {
  margin-inline-start: 1rem;
}

.ms-6 {
  margin-inline-start: 1.5rem;
}

.ms-auto {
  margin-inline-start: auto;
}

.mt-0 {
  margin-top: 0px;
}

.mt-0\\.5 {
  margin-top: 0.125rem;
}

.mt-1 {
  margin-top: 0.25rem;
}

.mt-10 {
  margin-top: 2.5rem;
}

.mt-12 {
  margin-top: 3rem;
}

.mt-16 {
  margin-top: 4rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.mt-3 {
  margin-top: 0.75rem;
}

.mt-32 {
  margin-top: 8rem;
}

.mt-4 {
  margin-top: 1rem;
}

.mt-5 {
  margin-top: 1.25rem;
}

.mt-6 {
  margin-top: 1.5rem;
}

.mt-8 {
  margin-top: 2rem;
}

.mt-auto {
  margin-top: auto;
}

.mt-px {
  margin-top: 1px;
}

.line-clamp-\\[--line-clamp\\] {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: var(--line-clamp);
}

.block {
  display: block;
}

.inline-block {
  display: inline-block;
}

.inline {
  display: inline;
}

.flex {
  display: flex;
}

.inline-flex {
  display: inline-flex;
}

.table {
  display: table;
}

.table-cell {
  display: table-cell;
}

.table-row {
  display: table-row;
}

.grid {
  display: grid;
}

.inline-grid {
  display: inline-grid;
}

.contents {
  display: contents;
}

.hidden {
  display: none;
}

.aspect-\\[3\\/4\\] {
  aspect-ratio: 3/4;
}

.aspect-square {
  aspect-ratio: 1 / 1;
}

.aspect-video {
  aspect-ratio: 16 / 9;
}

.h-0 {
  height: 0px;
}

.h-1 {
  height: 0.25rem;
}

.h-1\\.5 {
  height: 0.375rem;
}

.h-1\\/2 {
  height: 50%;
}

.h-10 {
  height: 2.5rem;
}

.h-11 {
  height: 2.75rem;
}

.h-12 {
  height: 3rem;
}

.h-14 {
  height: 3.5rem;
}

.h-16 {
  height: 4rem;
}

.h-2 {
  height: 0.5rem;
}

.h-2\\.5 {
  height: 0.625rem;
}

.h-20 {
  height: 5rem;
}

.h-24 {
  height: 6rem;
}

.h-28 {
  height: 7rem;
}

.h-3 {
  height: 0.75rem;
}

.h-3\\.5 {
  height: 0.875rem;
}

.h-32 {
  height: 8rem;
}

.h-4 {
  height: 1rem;
}

.h-40 {
  height: 10rem;
}

.h-48 {
  height: 12rem;
}

.h-5 {
  height: 1.25rem;
}

.h-5\\/6 {
  height: 83.333333%;
}

.h-52 {
  height: 13rem;
}

.h-6 {
  height: 1.5rem;
}

.h-64 {
  height: 16rem;
}

.h-7 {
  height: 1.75rem;
}

.h-8 {
  height: 2rem;
}

.h-9 {
  height: 2.25rem;
}

.h-96 {
  height: 24rem;
}

.h-\\[100dvh\\] {
  height: 100dvh;
}

.h-\\[60vh\\] {
  height: 60vh;
}

.h-\\[70vh\\] {
  height: 70vh;
}

.h-\\[75vh\\] {
  height: 75vh;
}

.h-\\[calc\\(100\\%-1rem\\)\\] {
  height: calc(100% - 1rem);
}

.h-auto {
  height: auto;
}

.h-dvh {
  height: 100dvh;
}

.h-full {
  height: 100%;
}

.h-screen {
  height: 100vh;
}

.max-h-20 {
  max-height: 5rem;
}

.max-h-24 {
  max-height: 6rem;
}

.max-h-32 {
  max-height: 8rem;
}

.max-h-96 {
  max-height: 24rem;
}

.max-h-\\[100dvh\\] {
  max-height: 100dvh;
}

.max-h-\\[50vh\\] {
  max-height: 50vh;
}

.max-h-\\[60vh\\] {
  max-height: 60vh;
}

.max-h-\\[90vh\\] {
  max-height: 90vh;
}

.max-h-\\[calc\\(100dvh-220px\\)\\] {
  max-height: calc(100dvh - 220px);
}

.max-h-full {
  max-height: 100%;
}

.min-h-5 {
  min-height: 1.25rem;
}

.min-h-\\[1\\.5rem\\] {
  min-height: 1.5rem;
}

.min-h-\\[100dvh\\] {
  min-height: 100dvh;
}

.min-h-\\[50vh\\] {
  min-height: 50vh;
}

.min-h-\\[64px\\] {
  min-height: 64px;
}

.min-h-\\[80vh\\] {
  min-height: 80vh;
}

.min-h-\\[theme\\(spacing\\.48\\)\\] {
  min-height: 12rem;
}

.min-h-full {
  min-height: 100%;
}

.min-h-screen {
  min-height: 100vh;
}

.w-0 {
  width: 0px;
}

.w-1 {
  width: 0.25rem;
}

.w-1\\.5 {
  width: 0.375rem;
}

.w-1\\/2 {
  width: 50%;
}

.w-10 {
  width: 2.5rem;
}

.w-11 {
  width: 2.75rem;
}

.w-11\\/12 {
  width: 91.666667%;
}

.w-12 {
  width: 3rem;
}

.w-14 {
  width: 3.5rem;
}

.w-16 {
  width: 4rem;
}

.w-2 {
  width: 0.5rem;
}

.w-20 {
  width: 5rem;
}

.w-24 {
  width: 6rem;
}

.w-28 {
  width: 7rem;
}

.w-3 {
  width: 0.75rem;
}

.w-3\\.5 {
  width: 0.875rem;
}

.w-3\\/4 {
  width: 75%;
}

.w-3\\/5 {
  width: 60%;
}

.w-32 {
  width: 8rem;
}

.w-4 {
  width: 1rem;
}

.w-40 {
  width: 10rem;
}

.w-48 {
  width: 12rem;
}

.w-5 {
  width: 1.25rem;
}

.w-5\\/12 {
  width: 41.666667%;
}

.w-6 {
  width: 1.5rem;
}

.w-64 {
  width: 16rem;
}

.w-7 {
  width: 1.75rem;
}

.w-72 {
  width: 18rem;
}

.w-8 {
  width: 2rem;
}

.w-80 {
  width: 20rem;
}

.w-9 {
  width: 2.25rem;
}

.w-\\[--sidebar-width\\] {
  width: var(--sidebar-width);
}

.w-\\[calc\\(100\\%\\+2rem\\)\\] {
  width: calc(100% + 2rem);
}

.w-auto {
  width: auto;
}

.w-full {
  width: 100%;
}

.w-max {
  width: -moz-max-content;
  width: max-content;
}

.w-px {
  width: 1px;
}

.w-screen {
  width: 100vw;
}

.min-w-0 {
  min-width: 0px;
}

.min-w-48 {
  min-width: 12rem;
}

.min-w-\\[240px\\] {
  min-width: 240px;
}

.min-w-\\[theme\\(spacing\\.4\\)\\] {
  min-width: 1rem;
}

.min-w-\\[theme\\(spacing\\.5\\)\\] {
  min-width: 1.25rem;
}

.min-w-\\[theme\\(spacing\\.6\\)\\] {
  min-width: 1.5rem;
}

.min-w-\\[theme\\(spacing\\.8\\)\\] {
  min-width: 2rem;
}

.min-w-full {
  min-width: 100%;
}

.\\!max-w-2xl {
  max-width: 42rem !important;
}

.\\!max-w-3xl {
  max-width: 48rem !important;
}

.\\!max-w-4xl {
  max-width: 56rem !important;
}

.\\!max-w-5xl {
  max-width: 64rem !important;
}

.\\!max-w-6xl {
  max-width: 72rem !important;
}

.\\!max-w-7xl {
  max-width: 80rem !important;
}

.\\!max-w-\\[14rem\\] {
  max-width: 14rem !important;
}

.\\!max-w-lg {
  max-width: 32rem !important;
}

.\\!max-w-md {
  max-width: 28rem !important;
}

.\\!max-w-sm {
  max-width: 24rem !important;
}

.\\!max-w-xl {
  max-width: 36rem !important;
}

.\\!max-w-xs {
  max-width: 20rem !important;
}

.max-w-2xl {
  max-width: 42rem;
}

.max-w-3xl {
  max-width: 48rem;
}

.max-w-4xl {
  max-width: 56rem;
}

.max-w-5xl {
  max-width: 64rem;
}

.max-w-6xl {
  max-width: 72rem;
}

.max-w-7xl {
  max-width: 80rem;
}

.max-w-\\[520px\\] {
  max-width: 520px;
}

.max-w-\\[550px\\] {
  max-width: 550px;
}

.max-w-\\[85\\%\\] {
  max-width: 85%;
}

.max-w-fit {
  max-width: -moz-fit-content;
  max-width: fit-content;
}

.max-w-full {
  max-width: 100%;
}

.max-w-lg {
  max-width: 32rem;
}

.max-w-max {
  max-width: -moz-max-content;
  max-width: max-content;
}

.max-w-md {
  max-width: 28rem;
}

.max-w-min {
  max-width: -moz-min-content;
  max-width: min-content;
}

.max-w-none {
  max-width: none;
}

.max-w-prose {
  max-width: 65ch;
}

.max-w-screen-2xl {
  max-width: 1536px;
}

.max-w-screen-lg {
  max-width: 1024px;
}

.max-w-screen-md {
  max-width: 768px;
}

.max-w-screen-sm {
  max-width: 640px;
}

.max-w-screen-xl {
  max-width: 1280px;
}

.max-w-sm {
  max-width: 24rem;
}

.max-w-xl {
  max-width: 36rem;
}

.max-w-xs {
  max-width: 20rem;
}

.flex-1 {
  flex: 1 1 0%;
}

.flex-none {
  flex: none;
}

.flex-shrink-0 {
  flex-shrink: 0;
}

.shrink-0 {
  flex-shrink: 0;
}

.flex-grow {
  flex-grow: 1;
}

.grow {
  flex-grow: 1;
}

.table-auto {
  table-layout: auto;
}

.border-collapse {
  border-collapse: collapse;
}

.origin-top {
  transform-origin: top;
}

.origin-top-left {
  transform-origin: top left;
}

.origin-top-right {
  transform-origin: top right;
}

.-translate-x-1\\/2 {
  --tw-translate-x: -50%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-x-1\\/4 {
  --tw-translate-x: -25%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-x-12 {
  --tw-translate-x: -3rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-x-5 {
  --tw-translate-x: -1.25rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-x-full {
  --tw-translate-x: -100%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-y-1\\/2 {
  --tw-translate-y: -50%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-y-12 {
  --tw-translate-y: -3rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-y-2 {
  --tw-translate-y: -0.5rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-translate-y-3\\/4 {
  --tw-translate-y: -75%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-x-0 {
  --tw-translate-x: 0px;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-x-12 {
  --tw-translate-x: 3rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-x-5 {
  --tw-translate-x: 1.25rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-x-full {
  --tw-translate-x: 100%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-y-0 {
  --tw-translate-y: 0px;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-y-12 {
  --tw-translate-y: 3rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-y-4 {
  --tw-translate-y: 1rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.translate-y-full {
  --tw-translate-y: 100%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.-rotate-180 {
  --tw-rotate: -180deg;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rotate-180 {
  --tw-rotate: 180deg;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rotate-45 {
  --tw-rotate: 45deg;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rotate-90 {
  --tw-rotate: 90deg;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.scale-100 {
  --tw-scale-x: 1;
  --tw-scale-y: 1;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.scale-125 {
  --tw-scale-x: 1.25;
  --tw-scale-y: 1.25;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.scale-90 {
  --tw-scale-x: .9;
  --tw-scale-y: .9;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.scale-95 {
  --tw-scale-x: .95;
  --tw-scale-y: .95;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.transform {
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

@keyframes fadeOut {

  0% {
    opacity: 1;
  }

  20% {
    opacity: 1;
  }

  100% {
    opacity: 0;
  }
}

.animate-fade {
  animation: fadeOut 5s ease-in-out;
}

@keyframes ping {

  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes pulse {

  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes spin {

  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.cursor-default {
  cursor: default;
}

.cursor-move {
  cursor: move;
}

.cursor-pointer {
  cursor: pointer;
}

.cursor-wait {
  cursor: wait;
}

.select-none {
  -webkit-user-select: none;
     -moz-user-select: none;
          user-select: none;
}

.select-all {
  -webkit-user-select: all;
     -moz-user-select: all;
          user-select: all;
}

.resize-none {
  resize: none;
}

.resize {
  resize: both;
}

.scroll-mt-9 {
  scroll-margin-top: 2.25rem;
}

.list-inside {
  list-style-position: inside;
}

.list-decimal {
  list-style-type: decimal;
}

.list-disc {
  list-style-type: disc;
}

.appearance-none {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
}

.columns-\\[--cols-default\\] {
  -moz-columns: var(--cols-default);
       columns: var(--cols-default);
}

.break-inside-avoid {
  -moz-column-break-inside: avoid;
       break-inside: avoid;
}

.auto-cols-fr {
  grid-auto-columns: minmax(0, 1fr);
}

.grid-flow-col {
  grid-auto-flow: column;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-12 {
  grid-template-columns: repeat(12, minmax(0, 1fr));
}

.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-7 {
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.grid-cols-\\[--cols-default\\] {
  grid-template-columns: var(--cols-default);
}

.grid-cols-\\[1fr_auto_1fr\\] {
  grid-template-columns: 1fr auto 1fr;
}

.grid-cols-\\[repeat\\(7\\2c minmax\\(theme\\(spacing\\.7\\)\\2c 1fr\\)\\)\\] {
  grid-template-columns: repeat(7,minmax(1.75rem,1fr));
}

.grid-cols-\\[repeat\\(auto-fit\\2c minmax\\(0\\2c 1fr\\)\\)\\] {
  grid-template-columns: repeat(auto-fit,minmax(0,1fr));
}

.grid-rows-\\[1fr_auto_1fr\\] {
  grid-template-rows: 1fr auto 1fr;
}

.flex-row {
  flex-direction: row;
}

.flex-row-reverse {
  flex-direction: row-reverse;
}

.flex-col {
  flex-direction: column;
}

.flex-col-reverse {
  flex-direction: column-reverse;
}

.flex-wrap {
  flex-wrap: wrap;
}

.place-items-center {
  place-items: center;
}

.content-start {
  align-content: flex-start;
}

.items-start {
  align-items: flex-start;
}

.items-end {
  align-items: flex-end;
}

.items-center {
  align-items: center;
}

.items-stretch {
  align-items: stretch;
}

.justify-start {
  justify-content: flex-start;
}

.justify-end {
  justify-content: flex-end;
}

.justify-center {
  justify-content: center;
}

.justify-between {
  justify-content: space-between;
}

.justify-items-start {
  justify-items: start;
}

.justify-items-center {
  justify-items: center;
}

.gap-0 {
  gap: 0px;
}

.gap-0\\.5 {
  gap: 0.125rem;
}

.gap-1 {
  gap: 0.25rem;
}

.gap-1\\.5 {
  gap: 0.375rem;
}

.gap-12 {
  gap: 3rem;
}

.gap-16 {
  gap: 4rem;
}

.gap-2 {
  gap: 0.5rem;
}

.gap-3 {
  gap: 0.75rem;
}

.gap-4 {
  gap: 1rem;
}

.gap-6 {
  gap: 1.5rem;
}

.gap-8 {
  gap: 2rem;
}

.gap-x-0 {
  -moz-column-gap: 0px;
       column-gap: 0px;
}

.gap-x-1 {
  -moz-column-gap: 0.25rem;
       column-gap: 0.25rem;
}

.gap-x-1\\.5 {
  -moz-column-gap: 0.375rem;
       column-gap: 0.375rem;
}

.gap-x-2 {
  -moz-column-gap: 0.5rem;
       column-gap: 0.5rem;
}

.gap-x-2\\.5 {
  -moz-column-gap: 0.625rem;
       column-gap: 0.625rem;
}

.gap-x-3 {
  -moz-column-gap: 0.75rem;
       column-gap: 0.75rem;
}

.gap-x-4 {
  -moz-column-gap: 1rem;
       column-gap: 1rem;
}

.gap-x-5 {
  -moz-column-gap: 1.25rem;
       column-gap: 1.25rem;
}

.gap-x-6 {
  -moz-column-gap: 1.5rem;
       column-gap: 1.5rem;
}

.gap-y-1 {
  row-gap: 0.25rem;
}

.gap-y-1\\.5 {
  row-gap: 0.375rem;
}

.gap-y-2 {
  row-gap: 0.5rem;
}

.gap-y-3 {
  row-gap: 0.75rem;
}

.gap-y-4 {
  row-gap: 1rem;
}

.gap-y-6 {
  row-gap: 1.5rem;
}

.gap-y-7 {
  row-gap: 1.75rem;
}

.gap-y-8 {
  row-gap: 2rem;
}

.gap-y-px {
  row-gap: 1px;
}

.-space-x-1 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-0.25rem * var(--tw-space-x-reverse));
  margin-left: calc(-0.25rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-2 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-0.5rem * var(--tw-space-x-reverse));
  margin-left: calc(-0.5rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-3 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-0.75rem * var(--tw-space-x-reverse));
  margin-left: calc(-0.75rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-4 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-1rem * var(--tw-space-x-reverse));
  margin-left: calc(-1rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-5 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-1.25rem * var(--tw-space-x-reverse));
  margin-left: calc(-1.25rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-6 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-1.5rem * var(--tw-space-x-reverse));
  margin-left: calc(-1.5rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-7 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-1.75rem * var(--tw-space-x-reverse));
  margin-left: calc(-1.75rem * calc(1 - var(--tw-space-x-reverse)));
}

.-space-x-8 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(-2rem * var(--tw-space-x-reverse));
  margin-left: calc(-2rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-0 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(0px * var(--tw-space-x-reverse));
  margin-left: calc(0px * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-1 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(0.25rem * var(--tw-space-x-reverse));
  margin-left: calc(0.25rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-1\\.5 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(0.375rem * var(--tw-space-x-reverse));
  margin-left: calc(0.375rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-2 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(0.5rem * var(--tw-space-x-reverse));
  margin-left: calc(0.5rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-3 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(0.75rem * var(--tw-space-x-reverse));
  margin-left: calc(0.75rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-4 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(1rem * var(--tw-space-x-reverse));
  margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-x-8 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 0;
  margin-right: calc(2rem * var(--tw-space-x-reverse));
  margin-left: calc(2rem * calc(1 - var(--tw-space-x-reverse)));
}

.space-y-0 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(0px * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(0px * var(--tw-space-y-reverse));
}

.space-y-0\\.5 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(0.125rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(0.125rem * var(--tw-space-y-reverse));
}

.space-y-1 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(0.25rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(0.25rem * var(--tw-space-y-reverse));
}

.space-y-12 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(3rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(3rem * var(--tw-space-y-reverse));
}

.space-y-2 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(0.5rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(0.5rem * var(--tw-space-y-reverse));
}

.space-y-24 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(6rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(6rem * var(--tw-space-y-reverse));
}

.space-y-3 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
}

.space-y-4 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(1rem * var(--tw-space-y-reverse));
}

.space-y-6 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(1.5rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(1.5rem * var(--tw-space-y-reverse));
}

.space-y-8 > :not([hidden]) ~ :not([hidden]) {
  --tw-space-y-reverse: 0;
  margin-top: calc(2rem * calc(1 - var(--tw-space-y-reverse)));
  margin-bottom: calc(2rem * var(--tw-space-y-reverse));
}

.divide-x > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-x-reverse: 0;
  border-right-width: calc(1px * var(--tw-divide-x-reverse));
  border-left-width: calc(1px * calc(1 - var(--tw-divide-x-reverse)));
}

.divide-y > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-y-reverse: 0;
  border-top-width: calc(1px * calc(1 - var(--tw-divide-y-reverse)));
  border-bottom-width: calc(1px * var(--tw-divide-y-reverse));
}

.divide-gray-100 > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-opacity: 1;
  border-color: rgba(var(--gray-100), var(--tw-divide-opacity));
}

.divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-opacity: 1;
  border-color: rgba(var(--gray-200), var(--tw-divide-opacity));
}

.divide-gray-300 > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-opacity: 1;
  border-color: rgba(var(--gray-300), var(--tw-divide-opacity));
}

.divide-gray-700 > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-opacity: 1;
  border-color: rgba(var(--gray-700), var(--tw-divide-opacity));
}

.self-start {
  align-self: flex-start;
}

.self-end {
  align-self: flex-end;
}

.self-center {
  align-self: center;
}

.self-stretch {
  align-self: stretch;
}

.justify-self-start {
  justify-self: start;
}

.justify-self-end {
  justify-self: end;
}

.justify-self-center {
  justify-self: center;
}

.overflow-auto {
  overflow: auto;
}

.overflow-hidden {
  overflow: hidden;
}

.overflow-x-auto {
  overflow-x: auto;
}

.overflow-y-auto {
  overflow-y: auto;
}

.overflow-x-hidden {
  overflow-x: hidden;
}

.overflow-y-hidden {
  overflow-y: hidden;
}

.overflow-x-clip {
  overflow-x: clip;
}

.overflow-y-scroll {
  overflow-y: scroll;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.whitespace-normal {
  white-space: normal;
}

.whitespace-nowrap {
  white-space: nowrap;
}

.whitespace-pre-wrap {
  white-space: pre-wrap;
}

.break-words {
  overflow-wrap: break-word;
}

.\\!rounded-full {
  border-radius: 9999px !important;
}

.\\!rounded-lg {
  border-radius: 0.5rem !important;
}

.\\!rounded-none {
  border-radius: 0px !important;
}

.rounded {
  border-radius: 0.25rem;
}

.rounded-2xl {
  border-radius: 1rem;
}

.rounded-3xl {
  border-radius: 1.5rem;
}

.rounded-full {
  border-radius: 9999px;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.rounded-md {
  border-radius: 0.375rem;
}

.rounded-sm {
  border-radius: 0.125rem;
}

.rounded-xl {
  border-radius: 0.75rem;
}

.\\!rounded-l-lg {
  border-top-left-radius: 0.5rem !important;
  border-bottom-left-radius: 0.5rem !important;
}

.\\!rounded-l-none {
  border-top-left-radius: 0px !important;
  border-bottom-left-radius: 0px !important;
}

.\\!rounded-r-lg {
  border-top-right-radius: 0.5rem !important;
  border-bottom-right-radius: 0.5rem !important;
}

.\\!rounded-r-none {
  border-top-right-radius: 0px !important;
  border-bottom-right-radius: 0px !important;
}

.rounded-b-lg {
  border-bottom-right-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
}

.rounded-b-xl {
  border-bottom-right-radius: 0.75rem;
  border-bottom-left-radius: 0.75rem;
}

.rounded-l-lg {
  border-top-left-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
}

.rounded-l-md {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.rounded-r-lg {
  border-top-right-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

.rounded-r-md {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
}

.rounded-t {
  border-top-left-radius: 0.25rem;
  border-top-right-radius: 0.25rem;
}

.rounded-t-3xl {
  border-top-left-radius: 1.5rem;
  border-top-right-radius: 1.5rem;
}

.rounded-t-lg {
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

.rounded-t-xl {
  border-top-left-radius: 0.75rem;
  border-top-right-radius: 0.75rem;
}

.rounded-bl {
  border-bottom-left-radius: 0.25rem;
}

.rounded-bl-lg {
  border-bottom-left-radius: 0.5rem;
}

.border {
  border-width: 1px;
}

.border-0 {
  border-width: 0px;
}

.border-2 {
  border-width: 2px;
}

.border-x-\\[0\\.5px\\] {
  border-left-width: 0.5px;
  border-right-width: 0.5px;
}

.border-y {
  border-top-width: 1px;
  border-bottom-width: 1px;
}

.\\!border-t-0 {
  border-top-width: 0px !important;
}

.border-b {
  border-bottom-width: 1px;
}

.border-b-0 {
  border-bottom-width: 0px;
}

.border-b-2 {
  border-bottom-width: 2px;
}

.border-e {
  border-inline-end-width: 1px;
}

.border-l {
  border-left-width: 1px;
}

.border-l-4 {
  border-left-width: 4px;
}

.border-r-4 {
  border-right-width: 4px;
}

.border-s {
  border-inline-start-width: 1px;
}

.border-t {
  border-top-width: 1px;
}

.border-t-4 {
  border-top-width: 4px;
}

.border-dashed {
  border-style: dashed;
}

.\\!border-none {
  border-style: none !important;
}

.border-none {
  border-style: none;
}

.border-\\[\\#4f4f58\\] {
  --tw-border-opacity: 1;
  border-color: rgb(79 79 88 / var(--tw-border-opacity));
}

.border-\\[\\#565869\\] {
  --tw-border-opacity: 1;
  border-color: rgb(86 88 105 / var(--tw-border-opacity));
}

.border-\\[\\#e0e0e0\\] {
  --tw-border-opacity: 1;
  border-color: rgb(224 224 224 / var(--tw-border-opacity));
}

.border-amber-200 {
  --tw-border-opacity: 1;
  border-color: rgb(253 230 138 / var(--tw-border-opacity));
}

.border-black {
  --tw-border-opacity: 1;
  border-color: rgb(0 0 0 / var(--tw-border-opacity));
}

.border-blue-200 {
  --tw-border-opacity: 1;
  border-color: rgb(191 219 254 / var(--tw-border-opacity));
}

.border-blue-300 {
  --tw-border-opacity: 1;
  border-color: rgb(147 197 253 / var(--tw-border-opacity));
}

.border-blue-500 {
  --tw-border-opacity: 1;
  border-color: rgb(59 130 246 / var(--tw-border-opacity));
}

.border-blue-700 {
  --tw-border-opacity: 1;
  border-color: rgb(29 78 216 / var(--tw-border-opacity));
}

.border-danger-600 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--danger-600), var(--tw-border-opacity));
}

.border-gray-100 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-100), var(--tw-border-opacity));
}

.border-gray-200 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-200), var(--tw-border-opacity));
}

.border-gray-300 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-300), var(--tw-border-opacity));
}

.border-gray-400 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-400), var(--tw-border-opacity));
}

.border-gray-500 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity));
}

.border-gray-600 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-600), var(--tw-border-opacity));
}

.border-green-200 {
  --tw-border-opacity: 1;
  border-color: rgb(187 247 208 / var(--tw-border-opacity));
}

.border-green-500 {
  --tw-border-opacity: 1;
  border-color: rgb(34 197 94 / var(--tw-border-opacity));
}

.border-indigo-400 {
  --tw-border-opacity: 1;
  border-color: rgb(129 140 248 / var(--tw-border-opacity));
}

.border-indigo-500 {
  --tw-border-opacity: 1;
  border-color: rgb(99 102 241 / var(--tw-border-opacity));
}

.border-indigo-600 {
  --tw-border-opacity: 1;
  border-color: rgb(79 70 229 / var(--tw-border-opacity));
}

.border-lime-800 {
  --tw-border-opacity: 1;
  border-color: rgb(63 98 18 / var(--tw-border-opacity));
}

.border-neutral-50 {
  --tw-border-opacity: 1;
  border-color: rgb(250 250 250 / var(--tw-border-opacity));
}

.border-orange-500 {
  --tw-border-opacity: 1;
  border-color: rgb(249 115 22 / var(--tw-border-opacity));
}

.border-primary-500 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.border-primary-600 {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-600), var(--tw-border-opacity));
}

.border-red-200 {
  --tw-border-opacity: 1;
  border-color: rgb(254 202 202 / var(--tw-border-opacity));
}

.border-red-500 {
  --tw-border-opacity: 1;
  border-color: rgb(239 68 68 / var(--tw-border-opacity));
}

.border-slate-600 {
  --tw-border-opacity: 1;
  border-color: rgb(71 85 105 / var(--tw-border-opacity));
}

.border-slate-700 {
  --tw-border-opacity: 1;
  border-color: rgb(51 65 85 / var(--tw-border-opacity));
}

.border-transparent {
  border-color: transparent;
}

.border-yellow-200 {
  --tw-border-opacity: 1;
  border-color: rgb(254 240 138 / var(--tw-border-opacity));
}

.border-yellow-500 {
  --tw-border-opacity: 1;
  border-color: rgb(234 179 8 / var(--tw-border-opacity));
}

.border-t-gray-200 {
  --tw-border-opacity: 1;
  border-top-color: rgba(var(--gray-200), var(--tw-border-opacity));
}

.border-t-white {
  --tw-border-opacity: 1;
  border-top-color: rgb(255 255 255 / var(--tw-border-opacity));
}

.border-opacity-20 {
  --tw-border-opacity: 0.2;
}

.\\!bg-gray-50 {
  --tw-bg-opacity: 1 !important;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity)) !important;
}

.\\!bg-gray-700 {
  --tw-bg-opacity: 1 !important;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity)) !important;
}

.bg-\\[\\#0f172a\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(15 23 42 / var(--tw-bg-opacity));
}

.bg-\\[\\#111827\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(17 24 39 / var(--tw-bg-opacity));
}

.bg-\\[\\#333\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(51 51 51 / var(--tw-bg-opacity));
}

.bg-\\[\\#343541\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(52 53 65 / var(--tw-bg-opacity));
}

.bg-\\[\\#3b4b58\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(59 75 88 / var(--tw-bg-opacity));
}

.bg-\\[\\#40414f\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(64 65 79 / var(--tw-bg-opacity));
}

.bg-\\[\\#4f4f58\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(79 79 88 / var(--tw-bg-opacity));
}

.bg-\\[\\#6A64F1\\] {
  --tw-bg-opacity: 1;
  background-color: rgb(106 100 241 / var(--tw-bg-opacity));
}

.bg-\\[hsla\\(0\\2c 0\\%\\2c 0\\%\\2c 0\\.5\\)\\] {
  background-color: hsla(0,0%,0%,0.5);
}

.bg-amber-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 243 199 / var(--tw-bg-opacity));
}

.bg-amber-200 {
  --tw-bg-opacity: 1;
  background-color: rgb(253 230 138 / var(--tw-bg-opacity));
}

.bg-black {
  --tw-bg-opacity: 1;
  background-color: rgb(0 0 0 / var(--tw-bg-opacity));
}

.bg-black\\/40 {
  background-color: rgb(0 0 0 / 0.4);
}

.bg-black\\/50 {
  background-color: rgb(0 0 0 / 0.5);
}

.bg-black\\/60 {
  background-color: rgb(0 0 0 / 0.6);
}

.bg-black\\/70 {
  background-color: rgb(0 0 0 / 0.7);
}

.bg-blue-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(219 234 254 / var(--tw-bg-opacity));
}

.bg-blue-50 {
  --tw-bg-opacity: 1;
  background-color: rgb(239 246 255 / var(--tw-bg-opacity));
}

.bg-blue-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(59 130 246 / var(--tw-bg-opacity));
}

.bg-blue-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(37 99 235 / var(--tw-bg-opacity));
}

.bg-blue-700 {
  --tw-bg-opacity: 1;
  background-color: rgb(29 78 216 / var(--tw-bg-opacity));
}

.bg-custom-100 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-100), var(--tw-bg-opacity));
}

.bg-custom-50 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-50), var(--tw-bg-opacity));
}

.bg-custom-600 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-600), var(--tw-bg-opacity));
}

.bg-emerald-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(5 150 105 / var(--tw-bg-opacity));
}

.bg-gray-100 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
}

.bg-gray-200 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-200), var(--tw-bg-opacity));
}

.bg-gray-200\\/70 {
  background-color: rgba(var(--gray-200), 0.7);
}

.bg-gray-300 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-300), var(--tw-bg-opacity));
}

.bg-gray-400 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-400), var(--tw-bg-opacity));
}

.bg-gray-50 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.bg-gray-500 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-500), var(--tw-bg-opacity));
}

.bg-gray-600 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-600), var(--tw-bg-opacity));
}

.bg-gray-700 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.bg-gray-800 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-800), var(--tw-bg-opacity));
}

.bg-gray-900 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

.bg-gray-950 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-950), var(--tw-bg-opacity));
}

.bg-gray-950\\/50 {
  background-color: rgba(var(--gray-950), 0.5);
}

.bg-green-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(220 252 231 / var(--tw-bg-opacity));
}

.bg-green-50 {
  --tw-bg-opacity: 1;
  background-color: rgb(240 253 244 / var(--tw-bg-opacity));
}

.bg-green-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(34 197 94 / var(--tw-bg-opacity));
}

.bg-green-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(22 163 74 / var(--tw-bg-opacity));
}

.bg-green-700 {
  --tw-bg-opacity: 1;
  background-color: rgb(21 128 61 / var(--tw-bg-opacity));
}

.bg-green-800 {
  --tw-bg-opacity: 1;
  background-color: rgb(22 101 52 / var(--tw-bg-opacity));
}

.bg-indigo-50 {
  --tw-bg-opacity: 1;
  background-color: rgb(238 242 255 / var(--tw-bg-opacity));
}

.bg-indigo-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(99 102 241 / var(--tw-bg-opacity));
}

.bg-indigo-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(79 70 229 / var(--tw-bg-opacity));
}

.bg-lime-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(132 204 22 / var(--tw-bg-opacity));
}

.bg-lime-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(101 163 13 / var(--tw-bg-opacity));
}

.bg-orange-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(255 237 213 / var(--tw-bg-opacity));
}

.bg-primary-500 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-500), var(--tw-bg-opacity));
}

.bg-primary-500\\/20 {
  background-color: rgba(var(--primary-500), 0.2);
}

.bg-primary-600 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-600), var(--tw-bg-opacity));
}

.bg-primary-700 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-700), var(--tw-bg-opacity));
}

.bg-purple-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(243 232 255 / var(--tw-bg-opacity));
}

.bg-red-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 226 226 / var(--tw-bg-opacity));
}

.bg-red-200 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 202 202 / var(--tw-bg-opacity));
}

.bg-red-400 {
  --tw-bg-opacity: 1;
  background-color: rgb(248 113 113 / var(--tw-bg-opacity));
}

.bg-red-50 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 242 242 / var(--tw-bg-opacity));
}

.bg-red-500 {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.bg-red-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(220 38 38 / var(--tw-bg-opacity));
}

.bg-red-700 {
  --tw-bg-opacity: 1;
  background-color: rgb(185 28 28 / var(--tw-bg-opacity));
}

.bg-red-800 {
  --tw-bg-opacity: 1;
  background-color: rgb(153 27 27 / var(--tw-bg-opacity));
}

.bg-rose-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(225 29 72 / var(--tw-bg-opacity));
}

.bg-rose-600\\/90 {
  background-color: rgb(225 29 72 / 0.9);
}

.bg-sky-800 {
  --tw-bg-opacity: 1;
  background-color: rgb(7 89 133 / var(--tw-bg-opacity));
}

.bg-slate-700 {
  --tw-bg-opacity: 1;
  background-color: rgb(51 65 85 / var(--tw-bg-opacity));
}

.bg-slate-700\\/70 {
  background-color: rgb(51 65 85 / 0.7);
}

.bg-slate-900\\/80 {
  background-color: rgb(15 23 42 / 0.8);
}

.bg-transparent {
  background-color: transparent;
}

.bg-violet-400 {
  --tw-bg-opacity: 1;
  background-color: rgb(167 139 250 / var(--tw-bg-opacity));
}

.bg-white {
  --tw-bg-opacity: 1;
  background-color: rgb(255 255 255 / var(--tw-bg-opacity));
}

.bg-white\\/0 {
  background-color: rgb(255 255 255 / 0);
}

.bg-white\\/5 {
  background-color: rgb(255 255 255 / 0.05);
}

.bg-white\\/50 {
  background-color: rgb(255 255 255 / 0.5);
}

.bg-yellow-100 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 249 195 / var(--tw-bg-opacity));
}

.bg-yellow-300 {
  --tw-bg-opacity: 1;
  background-color: rgb(253 224 71 / var(--tw-bg-opacity));
}

.bg-yellow-50 {
  --tw-bg-opacity: 1;
  background-color: rgb(254 252 232 / var(--tw-bg-opacity));
}

.bg-yellow-600 {
  --tw-bg-opacity: 1;
  background-color: rgb(202 138 4 / var(--tw-bg-opacity));
}

.bg-opacity-10 {
  --tw-bg-opacity: 0.1;
}

.bg-opacity-15 {
  --tw-bg-opacity: 0.15;
}

.bg-opacity-20 {
  --tw-bg-opacity: 0.2;
}

.bg-opacity-25 {
  --tw-bg-opacity: 0.25;
}

.bg-opacity-30 {
  --tw-bg-opacity: 0.3;
}

.bg-opacity-40 {
  --tw-bg-opacity: 0.4;
}

.bg-opacity-50 {
  --tw-bg-opacity: 0.5;
}

.bg-opacity-75 {
  --tw-bg-opacity: 0.75;
}

.\\!bg-none {
  background-image: none !important;
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

.bg-gradient-to-t {
  background-image: linear-gradient(to top, var(--tw-gradient-stops));
}

.bg-gradient-to-tr {
  background-image: linear-gradient(to top right, var(--tw-gradient-stops));
}

.from-black\\/80 {
  --tw-gradient-from: rgb(0 0 0 / 0.8) var(--tw-gradient-from-position);
  --tw-gradient-to: rgb(0 0 0 / 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.from-gray-700 {
  --tw-gradient-from: rgba(var(--gray-700), 1) var(--tw-gradient-from-position);
  --tw-gradient-to: rgba(var(--gray-700), 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.from-gray-700\\/50 {
  --tw-gradient-from: rgba(var(--gray-700), 0.5) var(--tw-gradient-from-position);
  --tw-gradient-to: rgba(var(--gray-700), 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.from-indigo-400 {
  --tw-gradient-from: #818cf8 var(--tw-gradient-from-position);
  --tw-gradient-to: rgb(129 140 248 / 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.from-indigo-600 {
  --tw-gradient-from: #4f46e5 var(--tw-gradient-from-position);
  --tw-gradient-to: rgb(79 70 229 / 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.from-purple-500 {
  --tw-gradient-from: #a855f7 var(--tw-gradient-from-position);
  --tw-gradient-to: rgb(168 85 247 / 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.via-transparent {
  --tw-gradient-to: rgb(0 0 0 / 0)  var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), transparent var(--tw-gradient-via-position), var(--tw-gradient-to);
}

.to-cyan-400 {
  --tw-gradient-to: #22d3ee var(--tw-gradient-to-position);
}

.to-emerald-400 {
  --tw-gradient-to: #34d399 var(--tw-gradient-to-position);
}

.to-emerald-600 {
  --tw-gradient-to: #059669 var(--tw-gradient-to-position);
}

.to-transparent {
  --tw-gradient-to: transparent var(--tw-gradient-to-position);
}

.bg-cover {
  background-size: cover;
}

.bg-fixed {
  background-attachment: fixed;
}

.bg-clip-border {
  background-clip: border-box;
}

.bg-center {
  background-position: center;
}

.bg-no-repeat {
  background-repeat: no-repeat;
}

.fill-current {
  fill: currentColor;
}

.fill-primary-600 {
  fill: rgba(var(--primary-600), 1);
}

.stroke-gray-400 {
  stroke: rgba(var(--gray-400), 1);
}

.stroke-gray-600 {
  stroke: rgba(var(--gray-600), 1);
}

.stroke-gray-700 {
  stroke: rgba(var(--gray-700), 1);
}

.stroke-red-500 {
  stroke: #ef4444;
}

.object-contain {
  -o-object-fit: contain;
     object-fit: contain;
}

.object-cover {
  -o-object-fit: cover;
     object-fit: cover;
}

.object-center {
  -o-object-position: center;
     object-position: center;
}

.p-0 {
  padding: 0px;
}

.p-0\\.5 {
  padding: 0.125rem;
}

.p-1 {
  padding: 0.25rem;
}

.p-10 {
  padding: 2.5rem;
}

.p-12 {
  padding: 3rem;
}

.p-2 {
  padding: 0.5rem;
}

.p-2\\.5 {
  padding: 0.625rem;
}

.p-3 {
  padding: 0.75rem;
}

.p-4 {
  padding: 1rem;
}

.p-5 {
  padding: 1.25rem;
}

.p-6 {
  padding: 1.5rem;
}

.p-8 {
  padding: 2rem;
}

.px-0 {
  padding-left: 0px;
  padding-right: 0px;
}

.px-0\\.5 {
  padding-left: 0.125rem;
  padding-right: 0.125rem;
}

.px-1 {
  padding-left: 0.25rem;
  padding-right: 0.25rem;
}

.px-1\\.5 {
  padding-left: 0.375rem;
  padding-right: 0.375rem;
}

.px-2 {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.px-2\\.5 {
  padding-left: 0.625rem;
  padding-right: 0.625rem;
}

.px-3 {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.px-3\\.5 {
  padding-left: 0.875rem;
  padding-right: 0.875rem;
}

.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}

.px-5 {
  padding-left: 1.25rem;
  padding-right: 1.25rem;
}

.px-6 {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.px-8 {
  padding-left: 2rem;
  padding-right: 2rem;
}

.py-0 {
  padding-top: 0px;
  padding-bottom: 0px;
}

.py-0\\.5 {
  padding-top: 0.125rem;
  padding-bottom: 0.125rem;
}

.py-1 {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.py-1\\.5 {
  padding-top: 0.375rem;
  padding-bottom: 0.375rem;
}

.py-10 {
  padding-top: 2.5rem;
  padding-bottom: 2.5rem;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.py-16 {
  padding-top: 4rem;
  padding-bottom: 4rem;
}

.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.py-2\\.5 {
  padding-top: 0.625rem;
  padding-bottom: 0.625rem;
}

.py-24 {
  padding-top: 6rem;
  padding-bottom: 6rem;
}

.py-3 {
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
}

.py-3\\.5 {
  padding-top: 0.875rem;
  padding-bottom: 0.875rem;
}

.py-4 {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

.py-5 {
  padding-top: 1.25rem;
  padding-bottom: 1.25rem;
}

.py-6 {
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
}

.py-8 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.\\!pe-3 {
  padding-inline-end: 0.75rem !important;
}

.\\!ps-8 {
  padding-inline-start: 2rem !important;
}

.pb-1 {
  padding-bottom: 0.25rem;
}

.pb-1\\.5 {
  padding-bottom: 0.375rem;
}

.pb-12 {
  padding-bottom: 3rem;
}

.pb-2 {
  padding-bottom: 0.5rem;
}

.pb-3 {
  padding-bottom: 0.75rem;
}

.pb-32 {
  padding-bottom: 8rem;
}

.pb-4 {
  padding-bottom: 1rem;
}

.pb-6 {
  padding-bottom: 1.5rem;
}

.pb-8 {
  padding-bottom: 2rem;
}

.pb-\\[96px\\] {
  padding-bottom: 96px;
}

.pb-\\[env\\(safe-area-inset-bottom\\)\\] {
  padding-bottom: env(safe-area-inset-bottom);
}

.pe-0 {
  padding-inline-end: 0px;
}

.pe-1 {
  padding-inline-end: 0.25rem;
}

.pe-2 {
  padding-inline-end: 0.5rem;
}

.pe-3 {
  padding-inline-end: 0.75rem;
}

.pe-4 {
  padding-inline-end: 1rem;
}

.pe-6 {
  padding-inline-end: 1.5rem;
}

.pe-8 {
  padding-inline-end: 2rem;
}

.pl-0 {
  padding-left: 0px;
}

.pl-1 {
  padding-left: 0.25rem;
}

.pl-2 {
  padding-left: 0.5rem;
}

.pl-3 {
  padding-left: 0.75rem;
}

.pl-4 {
  padding-left: 1rem;
}

.pr-10 {
  padding-right: 2.5rem;
}

.pr-3 {
  padding-right: 0.75rem;
}

.pr-4 {
  padding-right: 1rem;
}

.ps-0 {
  padding-inline-start: 0px;
}

.ps-1 {
  padding-inline-start: 0.25rem;
}

.ps-2 {
  padding-inline-start: 0.5rem;
}

.ps-3 {
  padding-inline-start: 0.75rem;
}

.ps-4 {
  padding-inline-start: 1rem;
}

.ps-\\[5\\.25rem\\] {
  padding-inline-start: 5.25rem;
}

.pt-0 {
  padding-top: 0px;
}

.pt-1 {
  padding-top: 0.25rem;
}

.pt-10 {
  padding-top: 2.5rem;
}

.pt-2 {
  padding-top: 0.5rem;
}

.pt-3 {
  padding-top: 0.75rem;
}

.pt-4 {
  padding-top: 1rem;
}

.pt-5 {
  padding-top: 1.25rem;
}

.pt-6 {
  padding-top: 1.5rem;
}

.text-left {
  text-align: left;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.text-justify {
  text-align: justify;
}

.text-start {
  text-align: start;
}

.text-end {
  text-align: end;
}

.align-top {
  vertical-align: top;
}

.align-middle {
  vertical-align: middle;
}

.align-bottom {
  vertical-align: bottom;
}

.font-\\[sans-serif\\] {
  font-family: sans-serif;
}

.font-mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.font-sans {
  font-family: Figtree, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.font-serif {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}

.text-2xl {
  font-size: 1.5rem;
  line-height: 2rem;
}

.text-3xl {
  font-size: 1.875rem;
  line-height: 2.25rem;
}

.text-4xl {
  font-size: 2.25rem;
  line-height: 2.5rem;
}

.text-5xl {
  font-size: 3rem;
  line-height: 1;
}

.text-6xl {
  font-size: 3.75rem;
  line-height: 1;
}

.text-\\[11px\\] {
  font-size: 11px;
}

.text-\\[15px\\] {
  font-size: 15px;
}

.text-base {
  font-size: 1rem;
  line-height: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
  line-height: 1.75rem;
}

.text-sm {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.text-xl {
  font-size: 1.25rem;
  line-height: 1.75rem;
}

.text-xs {
  font-size: 0.75rem;
  line-height: 1rem;
}

.font-black {
  font-weight: 900;
}

.font-bold {
  font-weight: 700;
}

.font-extrabold {
  font-weight: 800;
}

.font-extralight {
  font-weight: 200;
}

.font-light {
  font-weight: 300;
}

.font-medium {
  font-weight: 500;
}

.font-normal {
  font-weight: 400;
}

.font-semibold {
  font-weight: 600;
}

.font-thin {
  font-weight: 100;
}

.uppercase {
  text-transform: uppercase;
}

.capitalize {
  text-transform: capitalize;
}

.normal-case {
  text-transform: none;
}

.italic {
  font-style: italic;
}

.not-italic {
  font-style: normal;
}

.leading-4 {
  line-height: 1rem;
}

.leading-5 {
  line-height: 1.25rem;
}

.leading-6 {
  line-height: 1.5rem;
}

.leading-7 {
  line-height: 1.75rem;
}

.leading-8 {
  line-height: 2rem;
}

.leading-9 {
  line-height: 2.25rem;
}

.leading-\\[3\\.25rem\\] {
  line-height: 3.25rem;
}

.leading-loose {
  line-height: 2;
}

.leading-none {
  line-height: 1;
}

.leading-normal {
  line-height: 1.5;
}

.leading-relaxed {
  line-height: 1.625;
}

.leading-snug {
  line-height: 1.375;
}

.leading-tight {
  line-height: 1.25;
}

.tracking-normal {
  letter-spacing: 0em;
}

.tracking-tight {
  letter-spacing: -0.025em;
}

.tracking-tighter {
  letter-spacing: -0.05em;
}

.tracking-wide {
  letter-spacing: 0.025em;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.tracking-widest {
  letter-spacing: 0.1em;
}

.\\!text-gray-500 {
  --tw-text-opacity: 1 !important;
  color: rgba(var(--gray-500), var(--tw-text-opacity)) !important;
}

.text-\\[\\#07074D\\] {
  --tw-text-opacity: 1;
  color: rgb(7 7 77 / var(--tw-text-opacity));
}

.text-\\[\\#333\\] {
  --tw-text-opacity: 1;
  color: rgb(51 51 51 / var(--tw-text-opacity));
}

.text-\\[\\#6B7280\\] {
  --tw-text-opacity: 1;
  color: rgb(107 114 128 / var(--tw-text-opacity));
}

.text-\\[\\#d1d5db\\] {
  --tw-text-opacity: 1;
  color: rgb(209 213 219 / var(--tw-text-opacity));
}

.text-\\[\\#e8e8ea\\] {
  --tw-text-opacity: 1;
  color: rgb(232 232 234 / var(--tw-text-opacity));
}

.text-black {
  --tw-text-opacity: 1;
  color: rgb(0 0 0 / var(--tw-text-opacity));
}

.text-blue-500 {
  --tw-text-opacity: 1;
  color: rgb(59 130 246 / var(--tw-text-opacity));
}

.text-blue-600 {
  --tw-text-opacity: 1;
  color: rgb(37 99 235 / var(--tw-text-opacity));
}

.text-blue-700 {
  --tw-text-opacity: 1;
  color: rgb(29 78 216 / var(--tw-text-opacity));
}

.text-blue-800 {
  --tw-text-opacity: 1;
  color: rgb(30 64 175 / var(--tw-text-opacity));
}

.text-current {
  color: currentColor;
}

.text-custom-400 {
  --tw-text-opacity: 1;
  color: rgba(var(--c-400), var(--tw-text-opacity));
}

.text-custom-50 {
  --tw-text-opacity: 1;
  color: rgba(var(--c-50), var(--tw-text-opacity));
}

.text-custom-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--c-500), var(--tw-text-opacity));
}

.text-custom-600 {
  --tw-text-opacity: 1;
  color: rgba(var(--c-600), var(--tw-text-opacity));
}

.text-custom-700\\/50 {
  color: rgba(var(--c-700), 0.5);
}

.text-danger-600 {
  --tw-text-opacity: 1;
  color: rgba(var(--danger-600), var(--tw-text-opacity));
}

.text-gray-100 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-100), var(--tw-text-opacity));
}

.text-gray-200 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-200), var(--tw-text-opacity));
}

.text-gray-300 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-300), var(--tw-text-opacity));
}

.text-gray-400 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.text-gray-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.text-gray-600 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-600), var(--tw-text-opacity));
}

.text-gray-700 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.text-gray-700\\/50 {
  color: rgba(var(--gray-700), 0.5);
}

.text-gray-800 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-800), var(--tw-text-opacity));
}

.text-gray-900 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-900), var(--tw-text-opacity));
}

.text-gray-950 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-950), var(--tw-text-opacity));
}

.text-green-500 {
  --tw-text-opacity: 1;
  color: rgb(34 197 94 / var(--tw-text-opacity));
}

.text-green-600 {
  --tw-text-opacity: 1;
  color: rgb(22 163 74 / var(--tw-text-opacity));
}

.text-green-700 {
  --tw-text-opacity: 1;
  color: rgb(21 128 61 / var(--tw-text-opacity));
}

.text-green-800 {
  --tw-text-opacity: 1;
  color: rgb(22 101 52 / var(--tw-text-opacity));
}

.text-indigo-500 {
  --tw-text-opacity: 1;
  color: rgb(99 102 241 / var(--tw-text-opacity));
}

.text-indigo-600 {
  --tw-text-opacity: 1;
  color: rgb(79 70 229 / var(--tw-text-opacity));
}

.text-indigo-700 {
  --tw-text-opacity: 1;
  color: rgb(67 56 202 / var(--tw-text-opacity));
}

.text-inherit {
  color: inherit;
}

.text-lime-800 {
  --tw-text-opacity: 1;
  color: rgb(63 98 18 / var(--tw-text-opacity));
}

.text-neutral-50 {
  --tw-text-opacity: 1;
  color: rgb(250 250 250 / var(--tw-text-opacity));
}

.text-orange-500 {
  --tw-text-opacity: 1;
  color: rgb(249 115 22 / var(--tw-text-opacity));
}

.text-orange-600 {
  --tw-text-opacity: 1;
  color: rgb(234 88 12 / var(--tw-text-opacity));
}

.text-orange-800 {
  --tw-text-opacity: 1;
  color: rgb(154 52 18 / var(--tw-text-opacity));
}

.text-primary-400 {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-400), var(--tw-text-opacity));
}

.text-primary-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-500), var(--tw-text-opacity));
}

.text-primary-600 {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-600), var(--tw-text-opacity));
}

.text-primary-700 {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-700), var(--tw-text-opacity));
}

.text-purple-600 {
  --tw-text-opacity: 1;
  color: rgb(147 51 234 / var(--tw-text-opacity));
}

.text-purple-800 {
  --tw-text-opacity: 1;
  color: rgb(107 33 168 / var(--tw-text-opacity));
}

.text-red-500 {
  --tw-text-opacity: 1;
  color: rgb(239 68 68 / var(--tw-text-opacity));
}

.text-red-600 {
  --tw-text-opacity: 1;
  color: rgb(220 38 38 / var(--tw-text-opacity));
}

.text-red-700 {
  --tw-text-opacity: 1;
  color: rgb(185 28 28 / var(--tw-text-opacity));
}

.text-red-800 {
  --tw-text-opacity: 1;
  color: rgb(153 27 27 / var(--tw-text-opacity));
}

.text-slate-200 {
  --tw-text-opacity: 1;
  color: rgb(226 232 240 / var(--tw-text-opacity));
}

.text-slate-300 {
  --tw-text-opacity: 1;
  color: rgb(203 213 225 / var(--tw-text-opacity));
}

.text-slate-400 {
  --tw-text-opacity: 1;
  color: rgb(148 163 184 / var(--tw-text-opacity));
}

.text-slate-600 {
  --tw-text-opacity: 1;
  color: rgb(71 85 105 / var(--tw-text-opacity));
}

.text-slate-700 {
  --tw-text-opacity: 1;
  color: rgb(51 65 85 / var(--tw-text-opacity));
}

.text-slate-800 {
  --tw-text-opacity: 1;
  color: rgb(30 41 59 / var(--tw-text-opacity));
}

.text-success-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--success-500), var(--tw-text-opacity));
}

.text-violet-400 {
  --tw-text-opacity: 1;
  color: rgb(167 139 250 / var(--tw-text-opacity));
}

.text-violet-600 {
  --tw-text-opacity: 1;
  color: rgb(124 58 237 / var(--tw-text-opacity));
}

.text-white {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.text-yellow-300 {
  --tw-text-opacity: 1;
  color: rgb(253 224 71 / var(--tw-text-opacity));
}

.text-yellow-600 {
  --tw-text-opacity: 1;
  color: rgb(202 138 4 / var(--tw-text-opacity));
}

.text-yellow-700 {
  --tw-text-opacity: 1;
  color: rgb(161 98 7 / var(--tw-text-opacity));
}

.text-yellow-800 {
  --tw-text-opacity: 1;
  color: rgb(133 77 14 / var(--tw-text-opacity));
}

.text-opacity-100 {
  --tw-text-opacity: 1;
}

.underline {
  text-decoration-line: underline;
}

.line-through {
  text-decoration-line: line-through;
}

.antialiased {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.placeholder-gray-400::-moz-placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-placeholder-opacity));
}

.placeholder-gray-400::placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-placeholder-opacity));
}

.placeholder-gray-500::-moz-placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-placeholder-opacity));
}

.placeholder-gray-500::placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-placeholder-opacity));
}

.placeholder-gray-700::-moz-placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-placeholder-opacity));
}

.placeholder-gray-700::placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-placeholder-opacity));
}

.placeholder-slate-400::-moz-placeholder {
  --tw-placeholder-opacity: 1;
  color: rgb(148 163 184 / var(--tw-placeholder-opacity));
}

.placeholder-slate-400::placeholder {
  --tw-placeholder-opacity: 1;
  color: rgb(148 163 184 / var(--tw-placeholder-opacity));
}

.accent-emerald-600 {
  accent-color: #059669;
}

.accent-indigo-600 {
  accent-color: #4f46e5;
}

.opacity-0 {
  opacity: 0;
}

.opacity-100 {
  opacity: 1;
}

.opacity-20 {
  opacity: 0.2;
}

.opacity-25 {
  opacity: 0.25;
}

.opacity-40 {
  opacity: 0.4;
}

.opacity-50 {
  opacity: 0.5;
}

.opacity-70 {
  opacity: 0.7;
}

.opacity-75 {
  opacity: 0.75;
}

.opacity-80 {
  opacity: 0.8;
}

.bg-blend-multiply {
  background-blend-mode: multiply;
}

.shadow {
  --tw-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-2xl {
  --tw-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  --tw-shadow-colored: 0 25px 50px -12px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-\\[0_2px_10px_-3px_rgba\\(6\\2c 81\\2c 237\\2c 0\\.3\\)\\] {
  --tw-shadow: 0 2px 10px -3px rgba(6,81,237,0.3);
  --tw-shadow-colored: 0 2px 10px -3px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-lg {
  --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-md {
  --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-none {
  --tw-shadow: 0 0 #0000;
  --tw-shadow-colored: 0 0 #0000;
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-sm {
  --tw-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --tw-shadow-colored: 0 1px 2px 0 var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-xl {
  --tw-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 20px 25px -5px var(--tw-shadow-color), 0 8px 10px -6px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.shadow-gray-500\\/20 {
  --tw-shadow-color: rgba(var(--gray-500), 0.2);
  --tw-shadow: var(--tw-shadow-colored);
}

.outline-none {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

.outline {
  outline-style: solid;
}

.ring {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-0 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-1 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-2 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-4 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(4px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.ring-inset {
  --tw-ring-inset: inset;
}

.ring-black {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(0 0 0 / var(--tw-ring-opacity));
}

.ring-blue-300 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(147 197 253 / var(--tw-ring-opacity));
}

.ring-custom-600 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--c-600), var(--tw-ring-opacity));
}

.ring-custom-600\\/10 {
  --tw-ring-color: rgba(var(--c-600), 0.1);
}

.ring-custom-600\\/20 {
  --tw-ring-color: rgba(var(--c-600), 0.2);
}

.ring-danger-600 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-600), var(--tw-ring-opacity));
}

.ring-emerald-400 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(52 211 153 / var(--tw-ring-opacity));
}

.ring-gray-200 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-200), var(--tw-ring-opacity));
}

.ring-gray-300 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-300), var(--tw-ring-opacity));
}

.ring-gray-600\\/10 {
  --tw-ring-color: rgba(var(--gray-600), 0.1);
}

.ring-gray-900\\/10 {
  --tw-ring-color: rgba(var(--gray-900), 0.1);
}

.ring-gray-950\\/10 {
  --tw-ring-color: rgba(var(--gray-950), 0.1);
}

.ring-gray-950\\/5 {
  --tw-ring-color: rgba(var(--gray-950), 0.05);
}

.ring-primary-500 {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.ring-white {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(255 255 255 / var(--tw-ring-opacity));
}

.ring-white\\/10 {
  --tw-ring-color: rgb(255 255 255 / 0.1);
}

.ring-opacity-5 {
  --tw-ring-opacity: 0.05;
}

.blur {
  --tw-blur: blur(8px);
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.blur-2xl {
  --tw-blur: blur(40px);
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.drop-shadow {
  --tw-drop-shadow: drop-shadow(0 1px 2px rgb(0 0 0 / 0.1)) drop-shadow(0 1px 1px rgb(0 0 0 / 0.06));
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.filter {
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

.backdrop-blur {
  --tw-backdrop-blur: blur(8px);
  -webkit-backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
          backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
}

.backdrop-blur-sm {
  --tw-backdrop-blur: blur(4px);
  -webkit-backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
          backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
}

.backdrop-filter {
  -webkit-backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
          backdrop-filter: var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia);
}

.transition {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-backdrop-filter;
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-colors {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-opacity {
  transition-property: opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.delay-100 {
  transition-delay: 100ms;
}

.duration-100 {
  transition-duration: 100ms;
}

.duration-150 {
  transition-duration: 150ms;
}

.duration-200 {
  transition-duration: 200ms;
}

.duration-300 {
  transition-duration: 300ms;
}

.duration-500 {
  transition-duration: 500ms;
}

.duration-75 {
  transition-duration: 75ms;
}

.ease-in {
  transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
}

.ease-in-out {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.ease-out {
  transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
}

.\\[transform\\:translateZ\\(0\\)\\] {
  transform: translateZ(0);
}

@tailwind typography;

.dark\\:prose-invert:is(.dark *) {
  --tw-prose-body: var(--tw-prose-invert-body);
  --tw-prose-headings: var(--tw-prose-invert-headings);
  --tw-prose-lead: var(--tw-prose-invert-lead);
  --tw-prose-links: var(--tw-prose-invert-links);
  --tw-prose-bold: var(--tw-prose-invert-bold);
  --tw-prose-counters: var(--tw-prose-invert-counters);
  --tw-prose-bullets: var(--tw-prose-invert-bullets);
  --tw-prose-hr: var(--tw-prose-invert-hr);
  --tw-prose-quotes: var(--tw-prose-invert-quotes);
  --tw-prose-quote-borders: var(--tw-prose-invert-quote-borders);
  --tw-prose-captions: var(--tw-prose-invert-captions);
  --tw-prose-kbd: var(--tw-prose-invert-kbd);
  --tw-prose-kbd-shadows: var(--tw-prose-invert-kbd-shadows);
  --tw-prose-code: var(--tw-prose-invert-code);
  --tw-prose-pre-code: var(--tw-prose-invert-pre-code);
  --tw-prose-pre-bg: var(--tw-prose-invert-pre-bg);
  --tw-prose-th-borders: var(--tw-prose-invert-th-borders);
  --tw-prose-td-borders: var(--tw-prose-invert-td-borders);
}

.selection\\:bg-red-500 *::-moz-selection {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.selection\\:bg-red-500 *::selection {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.selection\\:text-white *::-moz-selection {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.selection\\:text-white *::selection {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.selection\\:bg-red-500::-moz-selection {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.selection\\:bg-red-500::selection {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.selection\\:text-white::-moz-selection {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.selection\\:text-white::selection {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.placeholder\\:text-gray-400::-moz-placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.placeholder\\:text-gray-400::placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.before\\:absolute::before {
  content: var(--tw-content);
  position: absolute;
}

.before\\:inset-y-0::before {
  content: var(--tw-content);
  top: 0px;
  bottom: 0px;
}

.before\\:right-0::before {
  content: var(--tw-content);
  right: 0px;
}

.before\\:start-0::before {
  content: var(--tw-content);
  inset-inline-start: 0px;
}

.before\\:h-full::before {
  content: var(--tw-content);
  height: 100%;
}

.before\\:w-0::before {
  content: var(--tw-content);
  width: 0px;
}

.before\\:w-0\\.5::before {
  content: var(--tw-content);
  width: 0.125rem;
}

.before\\:w-\\[300px\\]::before {
  content: var(--tw-content);
  width: 300px;
}

.before\\:bg-blue-400::before {
  content: var(--tw-content);
  --tw-bg-opacity: 1;
  background-color: rgb(96 165 250 / var(--tw-bg-opacity));
}

.before\\:bg-primary-600::before {
  content: var(--tw-content);
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-600), var(--tw-bg-opacity));
}

.first\\:border-s-0:first-child {
  border-inline-start-width: 0px;
}

.first\\:border-t-0:first-child {
  border-top-width: 0px;
}

.last\\:border-e-0:last-child {
  border-inline-end-width: 0px;
}

.first-of-type\\:ps-1:first-of-type {
  padding-inline-start: 0.25rem;
}

.last-of-type\\:pe-1:last-of-type {
  padding-inline-end: 0.25rem;
}

.checked\\:ring-0:checked {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus-within\\:bg-gray-50:focus-within {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.focus-within\\:outline-none:focus-within {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

.focus-within\\:ring-2:focus-within {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus-within\\:ring-indigo-500:focus-within {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(99 102 241 / var(--tw-ring-opacity));
}

.focus-within\\:ring-orange-500:focus-within {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(249 115 22 / var(--tw-ring-opacity));
}

.focus-within\\:ring-offset-2:focus-within {
  --tw-ring-offset-width: 2px;
}

.hover\\:-translate-y-2:hover {
  --tw-translate-y: -0.5rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.hover\\:scale-105:hover {
  --tw-scale-x: 1.05;
  --tw-scale-y: 1.05;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.hover\\:transform:hover {
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.hover\\:border-gray-300:hover {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-300), var(--tw-border-opacity));
}

.hover\\:border-neutral-100:hover {
  --tw-border-opacity: 1;
  border-color: rgb(245 245 245 / var(--tw-border-opacity));
}

.hover\\:bg-\\[\\#222\\]:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(34 34 34 / var(--tw-bg-opacity));
}

.hover\\:bg-\\[\\#565869\\]:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(86 88 105 / var(--tw-bg-opacity));
}

.hover\\:bg-\\[\\#5e5e69\\]:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(94 94 105 / var(--tw-bg-opacity));
}

.hover\\:bg-blue-50:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(239 246 255 / var(--tw-bg-opacity));
}

.hover\\:bg-blue-600:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(37 99 235 / var(--tw-bg-opacity));
}

.hover\\:bg-blue-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(29 78 216 / var(--tw-bg-opacity));
}

.hover\\:bg-blue-800:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(30 64 175 / var(--tw-bg-opacity));
}

.hover\\:bg-custom-400\\/10:hover {
  background-color: rgba(var(--c-400), 0.1);
}

.hover\\:bg-custom-50:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-50), var(--tw-bg-opacity));
}

.hover\\:bg-custom-500:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-500), var(--tw-bg-opacity));
}

.hover\\:bg-emerald-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(4 120 87 / var(--tw-bg-opacity));
}

.hover\\:bg-gray-100:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
}

.hover\\:bg-gray-200:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-200), var(--tw-bg-opacity));
}

.hover\\:bg-gray-300:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-300), var(--tw-bg-opacity));
}

.hover\\:bg-gray-400:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-400), var(--tw-bg-opacity));
}

.hover\\:bg-gray-400\\/10:hover {
  background-color: rgba(var(--gray-400), 0.1);
}

.hover\\:bg-gray-50:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.hover\\:bg-gray-500:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-500), var(--tw-bg-opacity));
}

.hover\\:bg-gray-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.hover\\:bg-gray-900:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

.hover\\:bg-green-600:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(22 163 74 / var(--tw-bg-opacity));
}

.hover\\:bg-green-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(21 128 61 / var(--tw-bg-opacity));
}

.hover\\:bg-green-800:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(22 101 52 / var(--tw-bg-opacity));
}

.hover\\:bg-indigo-600:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(79 70 229 / var(--tw-bg-opacity));
}

.hover\\:bg-indigo-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(67 56 202 / var(--tw-bg-opacity));
}

.hover\\:bg-lime-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(77 124 15 / var(--tw-bg-opacity));
}

.hover\\:bg-neutral-100:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(245 245 245 / var(--tw-bg-opacity));
}

.hover\\:bg-orange-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(194 65 12 / var(--tw-bg-opacity));
}

.hover\\:bg-primary-800:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-800), var(--tw-bg-opacity));
}

.hover\\:bg-red-500:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(239 68 68 / var(--tw-bg-opacity));
}

.hover\\:bg-red-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(185 28 28 / var(--tw-bg-opacity));
}

.hover\\:bg-red-800:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(153 27 27 / var(--tw-bg-opacity));
}

.hover\\:bg-rose-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(190 18 60 / var(--tw-bg-opacity));
}

.hover\\:bg-slate-600:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(71 85 105 / var(--tw-bg-opacity));
}

.hover\\:bg-white:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(255 255 255 / var(--tw-bg-opacity));
}

.hover\\:bg-yellow-700:hover {
  --tw-bg-opacity: 1;
  background-color: rgb(161 98 7 / var(--tw-bg-opacity));
}

.hover\\:bg-opacity-10:hover {
  --tw-bg-opacity: 0.1;
}

.hover\\:bg-opacity-40:hover {
  --tw-bg-opacity: 0.4;
}

.hover\\:bg-opacity-50:hover {
  --tw-bg-opacity: 0.5;
}

.hover\\:text-blue-500:hover {
  --tw-text-opacity: 1;
  color: rgb(59 130 246 / var(--tw-text-opacity));
}

.hover\\:text-blue-600:hover {
  --tw-text-opacity: 1;
  color: rgb(37 99 235 / var(--tw-text-opacity));
}

.hover\\:text-blue-700:hover {
  --tw-text-opacity: 1;
  color: rgb(29 78 216 / var(--tw-text-opacity));
}

.hover\\:text-blue-800:hover {
  --tw-text-opacity: 1;
  color: rgb(30 64 175 / var(--tw-text-opacity));
}

.hover\\:text-custom-600:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--c-600), var(--tw-text-opacity));
}

.hover\\:text-custom-700\\/75:hover {
  color: rgba(var(--c-700), 0.75);
}

.hover\\:text-gray-300:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-300), var(--tw-text-opacity));
}

.hover\\:text-gray-400:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.hover\\:text-gray-500:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.hover\\:text-gray-600:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-600), var(--tw-text-opacity));
}

.hover\\:text-gray-700:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.hover\\:text-gray-700\\/75:hover {
  color: rgba(var(--gray-700), 0.75);
}

.hover\\:text-gray-800:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-800), var(--tw-text-opacity));
}

.hover\\:text-gray-900:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-900), var(--tw-text-opacity));
}

.hover\\:text-indigo-500:hover {
  --tw-text-opacity: 1;
  color: rgb(99 102 241 / var(--tw-text-opacity));
}

.hover\\:text-indigo-700:hover {
  --tw-text-opacity: 1;
  color: rgb(67 56 202 / var(--tw-text-opacity));
}

.hover\\:text-neutral-100:hover {
  --tw-text-opacity: 1;
  color: rgb(245 245 245 / var(--tw-text-opacity));
}

.hover\\:text-orange-500:hover {
  --tw-text-opacity: 1;
  color: rgb(249 115 22 / var(--tw-text-opacity));
}

.hover\\:text-primary-700:hover {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-700), var(--tw-text-opacity));
}

.hover\\:underline:hover {
  text-decoration-line: underline;
}

.hover\\:no-underline:hover {
  text-decoration-line: none;
}

.hover\\:opacity-100:hover {
  opacity: 1;
}

.hover\\:shadow-xl:hover {
  --tw-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 20px 25px -5px var(--tw-shadow-color), 0 8px 10px -6px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.focus\\:z-10:focus {
  z-index: 10;
}

.focus\\:rounded-sm:focus {
  border-radius: 0.125rem;
}

.focus\\:border-\\[\\#333\\]:focus {
  --tw-border-opacity: 1;
  border-color: rgb(51 51 51 / var(--tw-border-opacity));
}

.focus\\:border-\\[\\#6A64F1\\]:focus {
  --tw-border-opacity: 1;
  border-color: rgb(106 100 241 / var(--tw-border-opacity));
}

.focus\\:border-blue-300:focus {
  --tw-border-opacity: 1;
  border-color: rgb(147 197 253 / var(--tw-border-opacity));
}

.focus\\:border-gray-300:focus {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-300), var(--tw-border-opacity));
}

.focus\\:border-indigo-300:focus {
  --tw-border-opacity: 1;
  border-color: rgb(165 180 252 / var(--tw-border-opacity));
}

.focus\\:border-indigo-500:focus {
  --tw-border-opacity: 1;
  border-color: rgb(99 102 241 / var(--tw-border-opacity));
}

.focus\\:border-indigo-700:focus {
  --tw-border-opacity: 1;
  border-color: rgb(67 56 202 / var(--tw-border-opacity));
}

.focus\\:border-neutral-100:focus {
  --tw-border-opacity: 1;
  border-color: rgb(245 245 245 / var(--tw-border-opacity));
}

.focus\\:border-orange-500:focus {
  --tw-border-opacity: 1;
  border-color: rgb(249 115 22 / var(--tw-border-opacity));
}

.focus\\:border-primary-500:focus {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.focus\\:border-primary-600:focus {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-600), var(--tw-border-opacity));
}

.focus\\:bg-blue-700:focus {
  --tw-bg-opacity: 1;
  background-color: rgb(29 78 216 / var(--tw-bg-opacity));
}

.focus\\:bg-gray-100:focus {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
}

.focus\\:bg-gray-50:focus {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.focus\\:bg-gray-700:focus {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.focus\\:bg-green-700:focus {
  --tw-bg-opacity: 1;
  background-color: rgb(21 128 61 / var(--tw-bg-opacity));
}

.focus\\:bg-indigo-100:focus {
  --tw-bg-opacity: 1;
  background-color: rgb(224 231 255 / var(--tw-bg-opacity));
}

.focus\\:bg-indigo-700:focus {
  --tw-bg-opacity: 1;
  background-color: rgb(67 56 202 / var(--tw-bg-opacity));
}

.focus\\:bg-yellow-700:focus {
  --tw-bg-opacity: 1;
  background-color: rgb(161 98 7 / var(--tw-bg-opacity));
}

.focus\\:text-gray-500:focus {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.focus\\:text-gray-700:focus {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.focus\\:text-gray-800:focus {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-800), var(--tw-text-opacity));
}

.focus\\:text-indigo-800:focus {
  --tw-text-opacity: 1;
  color: rgb(55 48 163 / var(--tw-text-opacity));
}

.focus\\:text-neutral-100:focus {
  --tw-text-opacity: 1;
  color: rgb(245 245 245 / var(--tw-text-opacity));
}

.focus\\:shadow-md:focus {
  --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.focus\\:outline-none:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

.focus\\:outline:focus {
  outline-style: solid;
}

.focus\\:outline-2:focus {
  outline-width: 2px;
}

.focus\\:outline-red-500:focus {
  outline-color: #ef4444;
}

.focus\\:ring:focus {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\\:ring-0:focus {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\\:ring-1:focus {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\\:ring-2:focus {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\\:ring-4:focus {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(4px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\\:ring-inset:focus {
  --tw-ring-inset: inset;
}

.focus\\:ring-blue-200:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(191 219 254 / var(--tw-ring-opacity));
}

.focus\\:ring-blue-300:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(147 197 253 / var(--tw-ring-opacity));
}

.focus\\:ring-blue-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(59 130 246 / var(--tw-ring-opacity));
}

.focus\\:ring-danger-600:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-600), var(--tw-ring-opacity));
}

.focus\\:ring-gray-100:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-100), var(--tw-ring-opacity));
}

.focus\\:ring-gray-200:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-200), var(--tw-ring-opacity));
}

.focus\\:ring-gray-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-500), var(--tw-ring-opacity));
}

.focus\\:ring-green-300:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(134 239 172 / var(--tw-ring-opacity));
}

.focus\\:ring-green-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(34 197 94 / var(--tw-ring-opacity));
}

.focus\\:ring-indigo-200:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(199 210 254 / var(--tw-ring-opacity));
}

.focus\\:ring-indigo-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(99 102 241 / var(--tw-ring-opacity));
}

.focus\\:ring-orange-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(249 115 22 / var(--tw-ring-opacity));
}

.focus\\:ring-primary-300:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-300), var(--tw-ring-opacity));
}

.focus\\:ring-primary-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.focus\\:ring-primary-600:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-600), var(--tw-ring-opacity));
}

.focus\\:ring-red-300:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(252 165 165 / var(--tw-ring-opacity));
}

.focus\\:ring-red-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(239 68 68 / var(--tw-ring-opacity));
}

.focus\\:ring-yellow-500:focus {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(234 179 8 / var(--tw-ring-opacity));
}

.focus\\:ring-opacity-50:focus {
  --tw-ring-opacity: 0.5;
}

.focus\\:ring-offset-0:focus {
  --tw-ring-offset-width: 0px;
}

.focus\\:ring-offset-2:focus {
  --tw-ring-offset-width: 2px;
}

.checked\\:focus\\:ring-danger-500\\/50:focus:checked {
  --tw-ring-color: rgba(var(--danger-500), 0.5);
}

.checked\\:focus\\:ring-primary-500\\/50:focus:checked {
  --tw-ring-color: rgba(var(--primary-500), 0.5);
}

.focus-visible\\:z-10:focus-visible {
  z-index: 10;
}

.focus-visible\\:border-primary-500:focus-visible {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.focus-visible\\:bg-custom-50:focus-visible {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-50), var(--tw-bg-opacity));
}

.focus-visible\\:bg-gray-100:focus-visible {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
}

.focus-visible\\:bg-gray-50:focus-visible {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.focus-visible\\:text-custom-700\\/75:focus-visible {
  color: rgba(var(--c-700), 0.75);
}

.focus-visible\\:text-gray-500:focus-visible {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.focus-visible\\:text-gray-700\\/75:focus-visible {
  color: rgba(var(--gray-700), 0.75);
}

.focus-visible\\:outline-none:focus-visible {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

.focus-visible\\:ring-1:focus-visible {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus-visible\\:ring-2:focus-visible {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus-visible\\:ring-inset:focus-visible {
  --tw-ring-inset: inset;
}

.focus-visible\\:ring-custom-500\\/50:focus-visible {
  --tw-ring-color: rgba(var(--c-500), 0.5);
}

.focus-visible\\:ring-custom-600:focus-visible {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--c-600), var(--tw-ring-opacity));
}

.focus-visible\\:ring-gray-400\\/40:focus-visible {
  --tw-ring-color: rgba(var(--gray-400), 0.4);
}

.focus-visible\\:ring-primary-500:focus-visible {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.focus-visible\\:ring-primary-600:focus-visible {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-600), var(--tw-ring-opacity));
}

.focus-visible\\:ring-offset-1:focus-visible {
  --tw-ring-offset-width: 1px;
}

.active\\:border-neutral-200:active {
  --tw-border-opacity: 1;
  border-color: rgb(229 229 229 / var(--tw-border-opacity));
}

.active\\:bg-blue-900:active {
  --tw-bg-opacity: 1;
  background-color: rgb(30 58 138 / var(--tw-bg-opacity));
}

.active\\:bg-gray-100:active {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-100), var(--tw-bg-opacity));
}

.active\\:bg-gray-900:active {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

.active\\:bg-green-900:active {
  --tw-bg-opacity: 1;
  background-color: rgb(20 83 45 / var(--tw-bg-opacity));
}

.active\\:bg-indigo-900:active {
  --tw-bg-opacity: 1;
  background-color: rgb(49 46 129 / var(--tw-bg-opacity));
}

.active\\:bg-red-700:active {
  --tw-bg-opacity: 1;
  background-color: rgb(185 28 28 / var(--tw-bg-opacity));
}

.active\\:bg-yellow-900:active {
  --tw-bg-opacity: 1;
  background-color: rgb(113 63 18 / var(--tw-bg-opacity));
}

.active\\:text-gray-500:active {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.active\\:text-gray-700:active {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.active\\:text-neutral-200:active {
  --tw-text-opacity: 1;
  color: rgb(229 229 229 / var(--tw-text-opacity));
}

.enabled\\:cursor-wait:enabled {
  cursor: wait;
}

.enabled\\:opacity-70:enabled {
  opacity: 0.7;
}

.disabled\\:pointer-events-none:disabled {
  pointer-events: none;
}

.disabled\\:cursor-not-allowed:disabled {
  cursor: not-allowed;
}

.disabled\\:bg-blue-400:disabled {
  --tw-bg-opacity: 1;
  background-color: rgb(96 165 250 / var(--tw-bg-opacity));
}

.disabled\\:bg-gray-50:disabled {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.disabled\\:text-gray-50:disabled {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-50), var(--tw-text-opacity));
}

.disabled\\:text-gray-500:disabled {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.disabled\\:opacity-25:disabled {
  opacity: 0.25;
}

.disabled\\:opacity-50:disabled {
  opacity: 0.5;
}

.disabled\\:opacity-70:disabled {
  opacity: 0.7;
}

.disabled\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.500\\)\\]:disabled {
  -webkit-text-fill-color: rgba(var(--gray-500), 1);
}

.disabled\\:placeholder\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.400\\)\\]:disabled::-moz-placeholder {
  -webkit-text-fill-color: rgba(var(--gray-400), 1);
}

.disabled\\:placeholder\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.400\\)\\]:disabled::placeholder {
  -webkit-text-fill-color: rgba(var(--gray-400), 1);
}

.disabled\\:checked\\:bg-gray-400:checked:disabled {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-400), var(--tw-bg-opacity));
}

.disabled\\:checked\\:text-gray-400:checked:disabled {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.group\\/item:first-child .group-first\\/item\\:rounded-s-lg {
  border-start-start-radius: 0.5rem;
  border-end-start-radius: 0.5rem;
}

.group\\/item:last-child .group-last\\/item\\:rounded-e-lg {
  border-start-end-radius: 0.5rem;
  border-end-end-radius: 0.5rem;
}

.group:hover .group-hover\\:stroke-gray-600 {
  stroke: rgba(var(--gray-600), 1);
}

.group\\/button:hover .group-hover\\/button\\:text-gray-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.group:hover .group-hover\\:text-gray-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.group:hover .group-hover\\:text-gray-700 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.group:hover .group-hover\\:text-indigo-600 {
  --tw-text-opacity: 1;
  color: rgb(79 70 229 / var(--tw-text-opacity));
}

.group:hover .group-hover\\:text-white {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.group\\/item:hover .group-hover\\/item\\:underline {
  text-decoration-line: underline;
}

.group\\/link:hover .group-hover\\/link\\:underline {
  text-decoration-line: underline;
}

.group:focus-visible .group-focus-visible\\:text-gray-500 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.group:focus-visible .group-focus-visible\\:text-gray-700 {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.group\\/item:focus-visible .group-focus-visible\\/item\\:underline {
  text-decoration-line: underline;
}

.group\\/link:focus-visible .group-focus-visible\\/link\\:underline {
  text-decoration-line: underline;
}

@media (prefers-reduced-motion: no-preference) {

  .motion-safe\\:hover\\:scale-\\[1\\.01\\]:hover {
    --tw-scale-x: 1.01;
    --tw-scale-y: 1.01;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }
}

.dark\\:flex:is(.dark *) {
  display: flex;
}

.dark\\:hidden:is(.dark *) {
  display: none;
}

.dark\\:divide-gray-700:is(.dark *) > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-opacity: 1;
  border-color: rgba(var(--gray-700), var(--tw-divide-opacity));
}

.dark\\:divide-white\\/10:is(.dark *) > :not([hidden]) ~ :not([hidden]) {
  border-color: rgb(255 255 255 / 0.1);
}

.dark\\:divide-white\\/5:is(.dark *) > :not([hidden]) ~ :not([hidden]) {
  border-color: rgb(255 255 255 / 0.05);
}

.dark\\:border-danger-400:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--danger-400), var(--tw-border-opacity));
}

.dark\\:border-gray-600:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-600), var(--tw-border-opacity));
}

.dark\\:border-gray-700:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-700), var(--tw-border-opacity));
}

.dark\\:border-gray-800:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-800), var(--tw-border-opacity));
}

.dark\\:border-gray-900:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-900), var(--tw-border-opacity));
}

.dark\\:border-primary-400:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-400), var(--tw-border-opacity));
}

.dark\\:border-primary-500:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.dark\\:border-transparent:is(.dark *) {
  border-color: transparent;
}

.dark\\:border-violet-400:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgb(167 139 250 / var(--tw-border-opacity));
}

.dark\\:border-white\\/10:is(.dark *) {
  border-color: rgb(255 255 255 / 0.1);
}

.dark\\:border-white\\/5:is(.dark *) {
  border-color: rgb(255 255 255 / 0.05);
}

.dark\\:border-t-white\\/10:is(.dark *) {
  border-top-color: rgb(255 255 255 / 0.1);
}

.dark\\:\\!bg-gray-700:is(.dark *) {
  --tw-bg-opacity: 1 !important;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity)) !important;
}

.dark\\:bg-black\\/10:is(.dark *) {
  background-color: rgb(0 0 0 / 0.1);
}

.dark\\:bg-black\\/20:is(.dark *) {
  background-color: rgb(0 0 0 / 0.2);
}

.dark\\:bg-blue-500:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(59 130 246 / var(--tw-bg-opacity));
}

.dark\\:bg-blue-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(30 58 138 / var(--tw-bg-opacity));
}

.dark\\:bg-custom-400\\/10:is(.dark *) {
  background-color: rgba(var(--c-400), 0.1);
}

.dark\\:bg-custom-500:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-500), var(--tw-bg-opacity));
}

.dark\\:bg-custom-500\\/20:is(.dark *) {
  background-color: rgba(var(--c-500), 0.2);
}

.dark\\:bg-gray-400\\/10:is(.dark *) {
  background-color: rgba(var(--gray-400), 0.1);
}

.dark\\:bg-gray-500:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-500), var(--tw-bg-opacity));
}

.dark\\:bg-gray-500\\/20:is(.dark *) {
  background-color: rgba(var(--gray-500), 0.2);
}

.dark\\:bg-gray-600:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-600), var(--tw-bg-opacity));
}

.dark\\:bg-gray-700:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.dark\\:bg-gray-800:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-800), var(--tw-bg-opacity));
}

.dark\\:bg-gray-800\\/50:is(.dark *) {
  background-color: rgba(var(--gray-800), 0.5);
}

.dark\\:bg-gray-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

.dark\\:bg-gray-900\\/30:is(.dark *) {
  background-color: rgba(var(--gray-900), 0.3);
}

.dark\\:bg-gray-950:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-950), var(--tw-bg-opacity));
}

.dark\\:bg-gray-950\\/30:is(.dark *) {
  background-color: rgba(var(--gray-950), 0.3);
}

.dark\\:bg-gray-950\\/50:is(.dark *) {
  background-color: rgba(var(--gray-950), 0.5);
}

.dark\\:bg-gray-950\\/75:is(.dark *) {
  background-color: rgba(var(--gray-950), 0.75);
}

.dark\\:bg-green-600:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(22 163 74 / var(--tw-bg-opacity));
}

.dark\\:bg-green-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(20 83 45 / var(--tw-bg-opacity));
}

.dark\\:bg-orange-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(124 45 18 / var(--tw-bg-opacity));
}

.dark\\:bg-primary-400:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-400), var(--tw-bg-opacity));
}

.dark\\:bg-primary-500:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-500), var(--tw-bg-opacity));
}

.dark\\:bg-primary-600:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-600), var(--tw-bg-opacity));
}

.dark\\:bg-red-600:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(220 38 38 / var(--tw-bg-opacity));
}

.dark\\:bg-red-800\\/20:is(.dark *) {
  background-color: rgb(153 27 27 / 0.2);
}

.dark\\:bg-red-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(127 29 29 / var(--tw-bg-opacity));
}

.dark\\:bg-transparent:is(.dark *) {
  background-color: transparent;
}

.dark\\:bg-violet-400:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(167 139 250 / var(--tw-bg-opacity));
}

.dark\\:bg-white\\/10:is(.dark *) {
  background-color: rgb(255 255 255 / 0.1);
}

.dark\\:bg-white\\/5:is(.dark *) {
  background-color: rgb(255 255 255 / 0.05);
}

.dark\\:bg-yellow-900:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(113 63 18 / var(--tw-bg-opacity));
}

.dark\\:bg-gradient-to-bl:is(.dark *) {
  background-image: linear-gradient(to bottom left, var(--tw-gradient-stops));
}

.dark\\:stroke-gray-600:is(.dark *) {
  stroke: rgba(var(--gray-600), 1);
}

.dark\\:text-blue-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(191 219 254 / var(--tw-text-opacity));
}

.dark\\:text-blue-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(96 165 250 / var(--tw-text-opacity));
}

.dark\\:text-blue-500:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(59 130 246 / var(--tw-text-opacity));
}

.dark\\:text-custom-300\\/50:is(.dark *) {
  color: rgba(var(--c-300), 0.5);
}

.dark\\:text-custom-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--c-400), var(--tw-text-opacity));
}

.dark\\:text-custom-400\\/10:is(.dark *) {
  color: rgba(var(--c-400), 0.1);
}

.dark\\:text-danger-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--danger-400), var(--tw-text-opacity));
}

.dark\\:text-danger-500:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--danger-500), var(--tw-text-opacity));
}

.dark\\:text-gray-100:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-100), var(--tw-text-opacity));
}

.dark\\:text-gray-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-200), var(--tw-text-opacity));
}

.dark\\:text-gray-300:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-300), var(--tw-text-opacity));
}

.dark\\:text-gray-300\\/50:is(.dark *) {
  color: rgba(var(--gray-300), 0.5);
}

.dark\\:text-gray-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:text-gray-50:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-50), var(--tw-text-opacity));
}

.dark\\:text-gray-500:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.dark\\:text-gray-600:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-600), var(--tw-text-opacity));
}

.dark\\:text-gray-700:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-700), var(--tw-text-opacity));
}

.dark\\:text-gray-800:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-800), var(--tw-text-opacity));
}

.dark\\:text-gray-900:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-900), var(--tw-text-opacity));
}

.dark\\:text-green-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(187 247 208 / var(--tw-text-opacity));
}

.dark\\:text-green-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(74 222 128 / var(--tw-text-opacity));
}

.dark\\:text-indigo-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(129 140 248 / var(--tw-text-opacity));
}

.dark\\:text-orange-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(254 215 170 / var(--tw-text-opacity));
}

.dark\\:text-orange-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(251 146 60 / var(--tw-text-opacity));
}

.dark\\:text-primary-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-400), var(--tw-text-opacity));
}

.dark\\:text-primary-500:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-500), var(--tw-text-opacity));
}

.dark\\:text-red-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(254 202 202 / var(--tw-text-opacity));
}

.dark\\:text-violet-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(167 139 250 / var(--tw-text-opacity));
}

.dark\\:text-white:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.dark\\:text-white\\/5:is(.dark *) {
  color: rgb(255 255 255 / 0.05);
}

.dark\\:text-yellow-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(254 240 138 / var(--tw-text-opacity));
}

.dark\\:placeholder-gray-400:is(.dark *)::-moz-placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-placeholder-opacity));
}

.dark\\:placeholder-gray-400:is(.dark *)::placeholder {
  --tw-placeholder-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-placeholder-opacity));
}

.dark\\:shadow-none:is(.dark *) {
  --tw-shadow: 0 0 #0000;
  --tw-shadow-colored: 0 0 #0000;
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.dark\\:ring-1:is(.dark *) {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.dark\\:ring-inset:is(.dark *) {
  --tw-ring-inset: inset;
}

.dark\\:ring-custom-400\\/30:is(.dark *) {
  --tw-ring-color: rgba(var(--c-400), 0.3);
}

.dark\\:ring-custom-500:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--c-500), var(--tw-ring-opacity));
}

.dark\\:ring-danger-400:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-400), var(--tw-ring-opacity));
}

.dark\\:ring-danger-500:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-500), var(--tw-ring-opacity));
}

.dark\\:ring-gray-400\\/20:is(.dark *) {
  --tw-ring-color: rgba(var(--gray-400), 0.2);
}

.dark\\:ring-gray-50\\/10:is(.dark *) {
  --tw-ring-color: rgba(var(--gray-50), 0.1);
}

.dark\\:ring-gray-700:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-700), var(--tw-ring-opacity));
}

.dark\\:ring-gray-900:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-900), var(--tw-ring-opacity));
}

.dark\\:ring-white\\/10:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.1);
}

.dark\\:ring-white\\/20:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.2);
}

.dark\\:ring-white\\/5:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.05);
}

.dark\\:ring-offset-gray-800:is(.dark *) {
  --tw-ring-offset-color: rgba(var(--gray-800), 1);
}

.dark\\:placeholder\\:text-gray-400:is(.dark *)::-moz-placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:placeholder\\:text-gray-400:is(.dark *)::placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:placeholder\\:text-gray-500:is(.dark *)::-moz-placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.dark\\:placeholder\\:text-gray-500:is(.dark *)::placeholder {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-500), var(--tw-text-opacity));
}

.dark\\:before\\:bg-primary-500:is(.dark *)::before {
  content: var(--tw-content);
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-500), var(--tw-bg-opacity));
}

.dark\\:checked\\:bg-danger-500:checked:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--danger-500), var(--tw-bg-opacity));
}

.dark\\:checked\\:bg-primary-500:checked:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-500), var(--tw-bg-opacity));
}

.dark\\:focus-within\\:bg-white\\/5:focus-within:is(.dark *) {
  background-color: rgb(255 255 255 / 0.05);
}

.dark\\:hover\\:border-gray-500:hover:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--gray-500), var(--tw-border-opacity));
}

.dark\\:hover\\:bg-blue-600:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(37 99 235 / var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-custom-400:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-400), var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-custom-400\\/10:hover:is(.dark *) {
  background-color: rgba(var(--c-400), 0.1);
}

.dark\\:hover\\:bg-gray-600:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-600), var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-gray-700:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-green-700:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(21 128 61 / var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-primary-700:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-700), var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-red-700:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgb(185 28 28 / var(--tw-bg-opacity));
}

.dark\\:hover\\:bg-white\\/10:hover:is(.dark *) {
  background-color: rgb(255 255 255 / 0.1);
}

.dark\\:hover\\:bg-white\\/5:hover:is(.dark *) {
  background-color: rgb(255 255 255 / 0.05);
}

.dark\\:hover\\:text-custom-300:hover:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--c-300), var(--tw-text-opacity));
}

.dark\\:hover\\:text-custom-300\\/75:hover:is(.dark *) {
  color: rgba(var(--c-300), 0.75);
}

.dark\\:hover\\:text-gray-200:hover:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-200), var(--tw-text-opacity));
}

.dark\\:hover\\:text-gray-300:hover:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-300), var(--tw-text-opacity));
}

.dark\\:hover\\:text-gray-300\\/75:hover:is(.dark *) {
  color: rgba(var(--gray-300), 0.75);
}

.dark\\:hover\\:text-gray-400:hover:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:hover\\:text-white:hover:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.dark\\:hover\\:ring-white\\/20:hover:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.2);
}

.dark\\:focus\\:border-blue-700:focus:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgb(29 78 216 / var(--tw-border-opacity));
}

.dark\\:focus\\:border-blue-800:focus:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgb(30 64 175 / var(--tw-border-opacity));
}

.dark\\:focus\\:border-primary-500:focus:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.dark\\:focus\\:text-white:focus:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

.dark\\:focus\\:ring-blue-700:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(29 78 216 / var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-blue-800:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(30 64 175 / var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-blue-900:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(30 58 138 / var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-danger-500:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-500), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-gray-500:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-500), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-gray-700:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-700), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-gray-800:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--gray-800), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-green-900:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(20 83 45 / var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-primary-500:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-primary-600:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-600), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-primary-800:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-800), var(--tw-ring-opacity));
}

.dark\\:focus\\:ring-red-900:focus:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgb(127 29 29 / var(--tw-ring-opacity));
}

.dark\\:checked\\:focus\\:ring-danger-400\\/50:focus:checked:is(.dark *) {
  --tw-ring-color: rgba(var(--danger-400), 0.5);
}

.dark\\:checked\\:focus\\:ring-primary-400\\/50:focus:checked:is(.dark *) {
  --tw-ring-color: rgba(var(--primary-400), 0.5);
}

.dark\\:focus-visible\\:border-primary-500:focus-visible:is(.dark *) {
  --tw-border-opacity: 1;
  border-color: rgba(var(--primary-500), var(--tw-border-opacity));
}

.dark\\:focus-visible\\:bg-custom-400\\/10:focus-visible:is(.dark *) {
  background-color: rgba(var(--c-400), 0.1);
}

.dark\\:focus-visible\\:bg-white\\/5:focus-visible:is(.dark *) {
  background-color: rgb(255 255 255 / 0.05);
}

.dark\\:focus-visible\\:text-custom-300\\/75:focus-visible:is(.dark *) {
  color: rgba(var(--c-300), 0.75);
}

.dark\\:focus-visible\\:text-gray-300\\/75:focus-visible:is(.dark *) {
  color: rgba(var(--gray-300), 0.75);
}

.dark\\:focus-visible\\:text-gray-400:focus-visible:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:focus-visible\\:ring-custom-400\\/50:focus-visible:is(.dark *) {
  --tw-ring-color: rgba(var(--c-400), 0.5);
}

.dark\\:focus-visible\\:ring-custom-500:focus-visible:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--c-500), var(--tw-ring-opacity));
}

.dark\\:focus-visible\\:ring-primary-500:focus-visible:is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.dark\\:focus-visible\\:ring-offset-gray-900:focus-visible:is(.dark *) {
  --tw-ring-offset-color: rgba(var(--gray-900), 1);
}

.dark\\:active\\:bg-gray-700:active:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
}

.dark\\:active\\:text-gray-300:active:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-300), var(--tw-text-opacity));
}

.dark\\:disabled\\:bg-transparent:disabled:is(.dark *) {
  background-color: transparent;
}

.dark\\:disabled\\:text-gray-400:disabled:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.dark\\:disabled\\:ring-white\\/10:disabled:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.1);
}

.dark\\:disabled\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.400\\)\\]:disabled:is(.dark *) {
  -webkit-text-fill-color: rgba(var(--gray-400), 1);
}

.dark\\:disabled\\:placeholder\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.500\\)\\]:disabled:is(.dark *)::-moz-placeholder {
  -webkit-text-fill-color: rgba(var(--gray-500), 1);
}

.dark\\:disabled\\:placeholder\\:\\[-webkit-text-fill-color\\:theme\\(colors\\.gray\\.500\\)\\]:disabled:is(.dark *)::placeholder {
  -webkit-text-fill-color: rgba(var(--gray-500), 1);
}

.dark\\:disabled\\:checked\\:bg-gray-600:checked:disabled:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-600), var(--tw-bg-opacity));
}

.group:hover .dark\\:group-hover\\:stroke-gray-400:is(.dark *) {
  stroke: rgba(var(--gray-400), 1);
}

.group\\/button:hover .dark\\:group-hover\\/button\\:text-gray-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.group:hover .dark\\:group-hover\\:text-gray-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-200), var(--tw-text-opacity));
}

.group:hover .dark\\:group-hover\\:text-gray-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

.group:focus-visible .dark\\:group-focus-visible\\:text-gray-200:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-200), var(--tw-text-opacity));
}

.group:focus-visible .dark\\:group-focus-visible\\:text-gray-400:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--gray-400), var(--tw-text-opacity));
}

@media not all and (min-width: 1024px) {

  .max-lg\\:hidden {
    display: none;
  }

  .max-lg\\:flex-col {
    flex-direction: column;
  }

  .max-lg\\:items-center {
    align-items: center;
  }

  .max-lg\\:space-y-2 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-y-reverse: 0;
    margin-top: calc(0.5rem * calc(1 - var(--tw-space-y-reverse)));
    margin-bottom: calc(0.5rem * var(--tw-space-y-reverse));
  }

  .max-lg\\:pt-6 {
    padding-top: 1.5rem;
  }
}

@media not all and (min-width: 768px) {

  .max-md\\:mx-auto {
    margin-left: auto;
    margin-right: auto;
  }

  .max-md\\:min-h-\\[350px\\] {
    min-height: 350px;
  }

  .max-md\\:max-w-lg {
    max-width: 32rem;
  }

  .max-md\\:max-w-md {
    max-width: 28rem;
  }

  .max-md\\:before\\:hidden::before {
    content: var(--tw-content);
    display: none;
  }
}

@media (min-width: 640px) {

  .sm\\:not-sr-only {
    position: static;
    width: auto;
    height: auto;
    padding: 0;
    margin: 0;
    overflow: visible;
    clip: auto;
    white-space: normal;
  }

  .sm\\:fixed {
    position: fixed;
  }

  .sm\\:relative {
    position: relative;
  }

  .sm\\:inset-x-auto {
    left: auto;
    right: auto;
  }

  .sm\\:end-0 {
    inset-inline-end: 0px;
  }

  .sm\\:right-0 {
    right: 0px;
  }

  .sm\\:top-0 {
    top: 0px;
  }

  .sm\\:col-\\[--col-span-sm\\] {
    grid-column: var(--col-span-sm);
  }

  .sm\\:col-span-2 {
    grid-column: span 2 / span 2;
  }

  .sm\\:col-span-4 {
    grid-column: span 4 / span 4;
  }

  .sm\\:col-start-\\[--col-start-sm\\] {
    grid-column-start: var(--col-start-sm);
  }

  .sm\\:-mx-6 {
    margin-left: -1.5rem;
    margin-right: -1.5rem;
  }

  .sm\\:-my-2 {
    margin-top: -0.5rem;
    margin-bottom: -0.5rem;
  }

  .sm\\:-my-px {
    margin-top: -1px;
    margin-bottom: -1px;
  }

  .sm\\:mx-auto {
    margin-left: auto;
    margin-right: auto;
  }

  .sm\\:mb-5 {
    margin-bottom: 1.25rem;
  }

  .sm\\:ml-0 {
    margin-left: 0px;
  }

  .sm\\:ml-10 {
    margin-left: 2.5rem;
  }

  .sm\\:ml-6 {
    margin-left: 1.5rem;
  }

  .sm\\:ms-auto {
    margin-inline-start: auto;
  }

  .sm\\:mt-0 {
    margin-top: 0px;
  }

  .sm\\:mt-5 {
    margin-top: 1.25rem;
  }

  .sm\\:mt-6 {
    margin-top: 1.5rem;
  }

  .sm\\:mt-7 {
    margin-top: 1.75rem;
  }

  .sm\\:block {
    display: block;
  }

  .sm\\:inline {
    display: inline;
  }

  .sm\\:flex {
    display: flex;
  }

  .sm\\:table-cell {
    display: table-cell;
  }

  .sm\\:grid {
    display: grid;
  }

  .sm\\:inline-grid {
    display: inline-grid;
  }

  .sm\\:hidden {
    display: none;
  }

  .sm\\:h-32 {
    height: 8rem;
  }

  .sm\\:h-80 {
    height: 20rem;
  }

  .sm\\:max-h-\\[60vh\\] {
    max-height: 60vh;
  }

  .sm\\:w-2\\/5 {
    width: 40%;
  }

  .sm\\:w-32 {
    width: 8rem;
  }

  .sm\\:w-48 {
    width: 12rem;
  }

  .sm\\:w-80 {
    width: 20rem;
  }

  .sm\\:w-\\[calc\\(100\\%\\+3rem\\)\\] {
    width: calc(100% + 3rem);
  }

  .sm\\:w-auto {
    width: auto;
  }

  .sm\\:w-full {
    width: 100%;
  }

  .sm\\:w-screen {
    width: 100vw;
  }

  .sm\\:max-w-2xl {
    max-width: 42rem;
  }

  .sm\\:max-w-full {
    max-width: 100%;
  }

  .sm\\:max-w-lg {
    max-width: 32rem;
  }

  .sm\\:max-w-md {
    max-width: 28rem;
  }

  .sm\\:max-w-sm {
    max-width: 24rem;
  }

  .sm\\:max-w-xl {
    max-width: 36rem;
  }

  .sm\\:flex-1 {
    flex: 1 1 0%;
  }

  .sm\\:translate-y-0 {
    --tw-translate-y: 0px;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .sm\\:scale-100 {
    --tw-scale-x: 1;
    --tw-scale-y: 1;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .sm\\:scale-95 {
    --tw-scale-x: .95;
    --tw-scale-y: .95;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .sm\\:columns-\\[--cols-sm\\] {
    -moz-columns: var(--cols-sm);
         columns: var(--cols-sm);
  }

  .sm\\:grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .sm\\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .sm\\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .sm\\:grid-cols-\\[--cols-sm\\] {
    grid-template-columns: var(--cols-sm);
  }

  .sm\\:grid-cols-\\[repeat\\(auto-fit\\2c minmax\\(0\\2c 1fr\\)\\)\\] {
    grid-template-columns: repeat(auto-fit,minmax(0,1fr));
  }

  .sm\\:grid-rows-\\[1fr_auto_3fr\\] {
    grid-template-rows: 1fr auto 3fr;
  }

  .sm\\:flex-row {
    flex-direction: row;
  }

  .sm\\:flex-nowrap {
    flex-wrap: nowrap;
  }

  .sm\\:items-start {
    align-items: flex-start;
  }

  .sm\\:items-end {
    align-items: flex-end;
  }

  .sm\\:items-center {
    align-items: center;
  }

  .sm\\:justify-start {
    justify-content: flex-start;
  }

  .sm\\:justify-center {
    justify-content: center;
  }

  .sm\\:justify-between {
    justify-content: space-between;
  }

  .sm\\:gap-1 {
    gap: 0.25rem;
  }

  .sm\\:gap-3 {
    gap: 0.75rem;
  }

  .sm\\:gap-6 {
    gap: 1.5rem;
  }

  .sm\\:gap-x-4 {
    -moz-column-gap: 1rem;
         column-gap: 1rem;
  }

  .sm\\:space-x-4 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1rem * var(--tw-space-x-reverse));
    margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .sm\\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-y-reverse: 0;
    margin-top: calc(0px * calc(1 - var(--tw-space-y-reverse)));
    margin-bottom: calc(0px * var(--tw-space-y-reverse));
  }

  .sm\\:rounded-lg {
    border-radius: 0.5rem;
  }

  .sm\\:rounded-xl {
    border-radius: 0.75rem;
  }

  .sm\\:p-10 {
    padding: 2.5rem;
  }

  .sm\\:p-16 {
    padding: 4rem;
  }

  .sm\\:p-6 {
    padding: 1.5rem;
  }

  .sm\\:p-8 {
    padding: 2rem;
  }

  .sm\\:px-0 {
    padding-left: 0px;
    padding-right: 0px;
  }

  .sm\\:px-12 {
    padding-left: 3rem;
    padding-right: 3rem;
  }

  .sm\\:px-16 {
    padding-left: 4rem;
    padding-right: 4rem;
  }

  .sm\\:px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .sm\\:px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .sm\\:px-8 {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .sm\\:py-1 {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
  }

  .sm\\:py-1\\.5 {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
  }

  .sm\\:py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }

  .sm\\:py-4 {
    padding-top: 1rem;
    padding-bottom: 1rem;
  }

  .sm\\:pb-0 {
    padding-bottom: 0px;
  }

  .sm\\:pe-3 {
    padding-inline-end: 0.75rem;
  }

  .sm\\:pe-6 {
    padding-inline-end: 1.5rem;
  }

  .sm\\:pl-4 {
    padding-left: 1rem;
  }

  .sm\\:pr-6 {
    padding-right: 1.5rem;
  }

  .sm\\:pr-8 {
    padding-right: 2rem;
  }

  .sm\\:ps-3 {
    padding-inline-start: 0.75rem;
  }

  .sm\\:ps-6 {
    padding-inline-start: 1.5rem;
  }

  .sm\\:pt-0 {
    padding-top: 0px;
  }

  .sm\\:pt-1 {
    padding-top: 0.25rem;
  }

  .sm\\:pt-1\\.5 {
    padding-top: 0.375rem;
  }

  .sm\\:text-left {
    text-align: left;
  }

  .sm\\:text-center {
    text-align: center;
  }

  .sm\\:text-right {
    text-align: right;
  }

  .sm\\:text-3xl {
    font-size: 1.875rem;
    line-height: 2.25rem;
  }

  .sm\\:text-4xl {
    font-size: 2.25rem;
    line-height: 2.5rem;
  }

  .sm\\:text-5xl {
    font-size: 3rem;
    line-height: 1;
  }

  .sm\\:text-base {
    font-size: 1rem;
    line-height: 1.5rem;
  }

  .sm\\:text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .sm\\:text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
  }

  .sm\\:leading-6 {
    line-height: 1.5rem;
  }

  .sm\\:first-of-type\\:ps-3:first-of-type {
    padding-inline-start: 0.75rem;
  }

  .sm\\:first-of-type\\:ps-6:first-of-type {
    padding-inline-start: 1.5rem;
  }

  .sm\\:last-of-type\\:pe-3:last-of-type {
    padding-inline-end: 0.75rem;
  }

  .sm\\:last-of-type\\:pe-6:last-of-type {
    padding-inline-end: 1.5rem;
  }
}

@media (min-width: 768px) {

  .md\\:inset-0 {
    inset: 0px;
  }

  .md\\:bottom-4 {
    bottom: 1rem;
  }

  .md\\:order-first {
    order: -9999;
  }

  .md\\:order-last {
    order: 9999;
  }

  .md\\:col-\\[--col-span-md\\] {
    grid-column: var(--col-span-md);
  }

  .md\\:col-span-1 {
    grid-column: span 1 / span 1;
  }

  .md\\:col-span-2 {
    grid-column: span 2 / span 2;
  }

  .md\\:col-span-3 {
    grid-column: span 3 / span 3;
  }

  .md\\:col-span-4 {
    grid-column: span 4 / span 4;
  }

  .md\\:col-span-6 {
    grid-column: span 6 / span 6;
  }

  .md\\:col-start-\\[--col-start-md\\] {
    grid-column-start: var(--col-start-md);
  }

  .md\\:my-8 {
    margin-top: 2rem;
    margin-bottom: 2rem;
  }

  .md\\:mb-0 {
    margin-bottom: 0px;
  }

  .md\\:ml-10 {
    margin-left: 2.5rem;
  }

  .md\\:mt-0 {
    margin-top: 0px;
  }

  .md\\:block {
    display: block;
  }

  .md\\:flex {
    display: flex;
  }

  .md\\:table-cell {
    display: table-cell;
  }

  .md\\:inline-grid {
    display: inline-grid;
  }

  .md\\:hidden {
    display: none;
  }

  .md\\:h-24 {
    height: 6rem;
  }

  .md\\:h-64 {
    height: 16rem;
  }

  .md\\:w-1\\/2 {
    width: 50%;
  }

  .md\\:w-1\\/3 {
    width: 33.333333%;
  }

  .md\\:w-2\\/5 {
    width: 40%;
  }

  .md\\:w-24 {
    width: 6rem;
  }

  .md\\:w-4\\/6 {
    width: 66.666667%;
  }

  .md\\:w-6\\/12 {
    width: 50%;
  }

  .md\\:w-64 {
    width: 16rem;
  }

  .md\\:w-72 {
    width: 18rem;
  }

  .md\\:w-auto {
    width: auto;
  }

  .md\\:w-max {
    width: -moz-max-content;
    width: max-content;
  }

  .md\\:max-w-60 {
    max-width: 15rem;
  }

  .md\\:flex-1 {
    flex: 1 1 0%;
  }

  .md\\:columns-\\[--cols-md\\] {
    -moz-columns: var(--cols-md);
         columns: var(--cols-md);
  }

  .md\\:grid-flow-col {
    grid-auto-flow: column;
  }

  .md\\:grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }

  .md\\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .md\\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .md\\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .md\\:grid-cols-\\[--cols-md\\] {
    grid-template-columns: var(--cols-md);
  }

  .md\\:flex-row {
    flex-direction: row;
  }

  .md\\:flex-wrap {
    flex-wrap: wrap;
  }

  .md\\:items-start {
    align-items: flex-start;
  }

  .md\\:items-end {
    align-items: flex-end;
  }

  .md\\:items-center {
    align-items: center;
  }

  .md\\:justify-end {
    justify-content: flex-end;
  }

  .md\\:justify-between {
    justify-content: space-between;
  }

  .md\\:gap-1 {
    gap: 0.25rem;
  }

  .md\\:gap-2 {
    gap: 0.5rem;
  }

  .md\\:gap-3 {
    gap: 0.75rem;
  }

  .md\\:space-x-4 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1rem * var(--tw-space-x-reverse));
    margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .md\\:space-x-8 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(2rem * var(--tw-space-x-reverse));
    margin-left: calc(2rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .md\\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-y-reverse: 0;
    margin-top: calc(0px * calc(1 - var(--tw-space-y-reverse)));
    margin-bottom: calc(0px * var(--tw-space-y-reverse));
  }

  .md\\:divide-y-0 > :not([hidden]) ~ :not([hidden]) {
    --tw-divide-y-reverse: 0;
    border-top-width: calc(0px * calc(1 - var(--tw-divide-y-reverse)));
    border-bottom-width: calc(0px * var(--tw-divide-y-reverse));
  }

  .md\\:overflow-x-auto {
    overflow-x: auto;
  }

  .md\\:rounded-xl {
    border-radius: 0.75rem;
  }

  .md\\:p-10 {
    padding: 2.5rem;
  }

  .md\\:p-20 {
    padding: 5rem;
  }

  .md\\:p-5 {
    padding: 1.25rem;
  }

  .md\\:p-8 {
    padding: 2rem;
  }

  .md\\:px-12 {
    padding-left: 3rem;
    padding-right: 3rem;
  }

  .md\\:px-5 {
    padding-left: 1.25rem;
    padding-right: 1.25rem;
  }

  .md\\:px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .md\\:px-\\[46px\\] {
    padding-left: 46px;
    padding-right: 46px;
  }

  .md\\:py-0 {
    padding-top: 0px;
    padding-bottom: 0px;
  }

  .md\\:py-16 {
    padding-top: 4rem;
    padding-bottom: 4rem;
  }

  .md\\:py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }

  .md\\:py-8 {
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .md\\:pb-\\[12px\\] {
    padding-bottom: 12px;
  }

  .md\\:pe-6 {
    padding-inline-end: 1.5rem;
  }

  .md\\:ps-3 {
    padding-inline-start: 0.75rem;
  }

  .md\\:pt-5 {
    padding-top: 1.25rem;
  }

  .md\\:pt-\\[14px\\] {
    padding-top: 14px;
  }

  .md\\:text-left {
    text-align: left;
  }

  .md\\:text-right {
    text-align: right;
  }

  .md\\:text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
  }

  .md\\:text-4xl {
    font-size: 2.25rem;
    line-height: 2.5rem;
  }

  .md\\:text-5xl {
    font-size: 3rem;
    line-height: 1;
  }

  .md\\:text-6xl {
    font-size: 3.75rem;
    line-height: 1;
  }

  .md\\:text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .md\\:text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
  }
}

@media (min-width: 1024px) {

  .lg\\:sticky {
    position: sticky;
  }

  .lg\\:z-0 {
    z-index: 0;
  }

  .lg\\:order-1 {
    order: 1;
  }

  .lg\\:order-2 {
    order: 2;
  }

  .lg\\:order-3 {
    order: 3;
  }

  .lg\\:col-\\[--col-span-lg\\] {
    grid-column: var(--col-span-lg);
  }

  .lg\\:col-span-10 {
    grid-column: span 10 / span 10;
  }

  .lg\\:col-span-2 {
    grid-column: span 2 / span 2;
  }

  .lg\\:col-span-3 {
    grid-column: span 3 / span 3;
  }

  .lg\\:col-span-8 {
    grid-column: span 8 / span 8;
  }

  .lg\\:col-start-1 {
    grid-column-start: 1;
  }

  .lg\\:col-start-2 {
    grid-column-start: 2;
  }

  .lg\\:col-start-\\[--col-start-lg\\] {
    grid-column-start: var(--col-start-lg);
  }

  .lg\\:row-start-1 {
    grid-row-start: 1;
  }

  .lg\\:mx-0 {
    margin-left: 0px;
    margin-right: 0px;
  }

  .lg\\:my-8 {
    margin-top: 2rem;
    margin-bottom: 2rem;
  }

  .lg\\:-ml-16 {
    margin-left: -4rem;
  }

  .lg\\:mb-16 {
    margin-bottom: 4rem;
  }

  .lg\\:mb-6 {
    margin-bottom: 1.5rem;
  }

  .lg\\:mt-0 {
    margin-top: 0px;
  }

  .lg\\:block {
    display: block;
  }

  .lg\\:flex {
    display: flex;
  }

  .lg\\:table-cell {
    display: table-cell;
  }

  .lg\\:inline-grid {
    display: inline-grid;
  }

  .lg\\:hidden {
    display: none;
  }

  .lg\\:h-96 {
    height: 24rem;
  }

  .lg\\:h-full {
    height: 100%;
  }

  .lg\\:w-1\\/3 {
    width: 33.333333%;
  }

  .lg\\:w-2\\/3 {
    width: 66.666667%;
  }

  .lg\\:w-2\\/4 {
    width: 50%;
  }

  .lg\\:w-3\\/12 {
    width: 25%;
  }

  .lg\\:w-4\\/12 {
    width: 33.333333%;
  }

  .lg\\:w-9\\/12 {
    width: 75%;
  }

  .lg\\:w-96 {
    width: 24rem;
  }

  .lg\\:max-w-7xl {
    max-width: 80rem;
  }

  .lg\\:max-w-md {
    max-width: 28rem;
  }

  .lg\\:max-w-xs {
    max-width: 20rem;
  }

  .lg\\:-translate-x-full {
    --tw-translate-x: -100%;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .lg\\:translate-x-0 {
    --tw-translate-x: 0px;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .lg\\:columns-\\[--cols-lg\\] {
    -moz-columns: var(--cols-lg);
         columns: var(--cols-lg);
  }

  .lg\\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .lg\\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .lg\\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .lg\\:grid-cols-\\[--cols-lg\\] {
    grid-template-columns: var(--cols-lg);
  }

  .lg\\:flex-row {
    flex-direction: row;
  }

  .lg\\:flex-col {
    flex-direction: column;
  }

  .lg\\:flex-wrap {
    flex-wrap: wrap;
  }

  .lg\\:flex-nowrap {
    flex-wrap: nowrap;
  }

  .lg\\:items-start {
    align-items: flex-start;
  }

  .lg\\:items-end {
    align-items: flex-end;
  }

  .lg\\:items-center {
    align-items: center;
  }

  .lg\\:justify-start {
    justify-content: flex-start;
  }

  .lg\\:justify-between {
    justify-content: space-between;
  }

  .lg\\:justify-evenly {
    justify-content: space-evenly;
  }

  .lg\\:gap-1 {
    gap: 0.25rem;
  }

  .lg\\:gap-16 {
    gap: 4rem;
  }

  .lg\\:gap-24 {
    gap: 6rem;
  }

  .lg\\:gap-3 {
    gap: 0.75rem;
  }

  .lg\\:gap-8 {
    gap: 2rem;
  }

  .lg\\:gap-x-8 {
    -moz-column-gap: 2rem;
         column-gap: 2rem;
  }

  .lg\\:gap-y-0 {
    row-gap: 0px;
  }

  .lg\\:space-x-3 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(0.75rem * var(--tw-space-x-reverse));
    margin-left: calc(0.75rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .lg\\:space-x-4 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1rem * var(--tw-space-x-reverse));
    margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .lg\\:space-x-6 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1.5rem * var(--tw-space-x-reverse));
    margin-left: calc(1.5rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .lg\\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-y-reverse: 0;
    margin-top: calc(0px * calc(1 - var(--tw-space-y-reverse)));
    margin-bottom: calc(0px * var(--tw-space-y-reverse));
  }

  .lg\\:self-center {
    align-self: center;
  }

  .lg\\:rounded-bl-lg {
    border-bottom-left-radius: 0.5rem;
  }

  .lg\\:rounded-tr-none {
    border-top-right-radius: 0px;
  }

  .lg\\:bg-transparent {
    background-color: transparent;
  }

  .lg\\:p-8 {
    padding: 2rem;
  }

  .lg\\:px-10 {
    padding-left: 2.5rem;
    padding-right: 2.5rem;
  }

  .lg\\:px-48 {
    padding-left: 12rem;
    padding-right: 12rem;
  }

  .lg\\:px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .lg\\:px-8 {
    padding-left: 2rem;
    padding-right: 2rem;
  }

  .lg\\:py-16 {
    padding-top: 4rem;
    padding-bottom: 4rem;
  }

  .lg\\:py-24 {
    padding-top: 6rem;
    padding-bottom: 6rem;
  }

  .lg\\:py-56 {
    padding-top: 14rem;
    padding-bottom: 14rem;
  }

  .lg\\:py-8 {
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .lg\\:pe-8 {
    padding-inline-end: 2rem;
  }

  .lg\\:pr-8 {
    padding-right: 2rem;
  }

  .lg\\:text-left {
    text-align: left;
  }

  .lg\\:text-center {
    text-align: center;
  }

  .lg\\:text-right {
    text-align: right;
  }

  .lg\\:text-6xl {
    font-size: 3.75rem;
    line-height: 1;
  }

  .lg\\:text-7xl {
    font-size: 4.5rem;
    line-height: 1;
  }

  .lg\\:text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
  }

  .lg\\:shadow-none {
    --tw-shadow: 0 0 #0000;
    --tw-shadow-colored: 0 0 #0000;
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
  }

  .lg\\:shadow-sm {
    --tw-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --tw-shadow-colored: 0 1px 2px 0 var(--tw-shadow-color);
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
  }

  .lg\\:ring-0 {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
  }

  .lg\\:transition {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-backdrop-filter;
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
  }

  .lg\\:transition-none {
    transition-property: none;
  }

  .lg\\:delay-100 {
    transition-delay: 100ms;
  }

  .dark\\:lg\\:bg-transparent:is(.dark *) {
    background-color: transparent;
  }
}

@media (min-width: 1280px) {

  .xl\\:col-\\[--col-span-xl\\] {
    grid-column: var(--col-span-xl);
  }

  .xl\\:col-start-\\[--col-start-xl\\] {
    grid-column-start: var(--col-start-xl);
  }

  .xl\\:block {
    display: block;
  }

  .xl\\:table-cell {
    display: table-cell;
  }

  .xl\\:inline-grid {
    display: inline-grid;
  }

  .xl\\:hidden {
    display: none;
  }

  .xl\\:w-1\\/4 {
    width: 25%;
  }

  .xl\\:columns-\\[--cols-xl\\] {
    -moz-columns: var(--cols-xl);
         columns: var(--cols-xl);
  }

  .xl\\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .xl\\:grid-cols-\\[--cols-xl\\] {
    grid-template-columns: var(--cols-xl);
  }

  .xl\\:flex-row {
    flex-direction: row;
  }

  .xl\\:items-start {
    align-items: flex-start;
  }

  .xl\\:items-end {
    align-items: flex-end;
  }

  .xl\\:items-center {
    align-items: center;
  }

  .xl\\:gap-1 {
    gap: 0.25rem;
  }

  .xl\\:gap-3 {
    gap: 0.75rem;
  }

  .xl\\:p-7 {
    padding: 1.75rem;
  }

  .xl\\:text-6xl {
    font-size: 3.75rem;
    line-height: 1;
  }

  .xl\\:text-7xl {
    font-size: 4.5rem;
    line-height: 1;
  }
}

@media (min-width: 1536px) {

  .\\32xl\\:col-\\[--col-span-2xl\\] {
    grid-column: var(--col-span-2xl);
  }

  .\\32xl\\:col-start-\\[--col-start-2xl\\] {
    grid-column-start: var(--col-start-2xl);
  }

  .\\32xl\\:block {
    display: block;
  }

  .\\32xl\\:table-cell {
    display: table-cell;
  }

  .\\32xl\\:inline-grid {
    display: inline-grid;
  }

  .\\32xl\\:hidden {
    display: none;
  }

  .\\32xl\\:columns-\\[--cols-2xl\\] {
    -moz-columns: var(--cols-2xl);
         columns: var(--cols-2xl);
  }

  .\\32xl\\:grid-cols-\\[--cols-2xl\\] {
    grid-template-columns: var(--cols-2xl);
  }

  .\\32xl\\:flex-row {
    flex-direction: row;
  }

  .\\32xl\\:items-start {
    align-items: flex-start;
  }

  .\\32xl\\:items-end {
    align-items: flex-end;
  }

  .\\32xl\\:items-center {
    align-items: center;
  }

  .\\32xl\\:gap-1 {
    gap: 0.25rem;
  }

  .\\32xl\\:gap-3 {
    gap: 0.75rem;
  }

  .\\32xl\\:px-0 {
    padding-left: 0px;
    padding-right: 0px;
  }
}

.ltr\\:hidden:where([dir="ltr"], [dir="ltr"] *) {
  display: none;
}

.rtl\\:left-0:where([dir="rtl"], [dir="rtl"] *) {
  left: 0px;
}

.rtl\\:right-2:where([dir="rtl"], [dir="rtl"] *) {
  right: 0.5rem;
}

.rtl\\:hidden:where([dir="rtl"], [dir="rtl"] *) {
  display: none;
}

.rtl\\:-translate-x-0:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: -0px;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:-translate-x-5:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: -1.25rem;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:-translate-x-full:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: -100%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:translate-x-1\\/2:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: 50%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:translate-x-1\\/4:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: 25%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:translate-x-full:where([dir="rtl"], [dir="rtl"] *) {
  --tw-translate-x: 100%;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:rotate-180:where([dir="rtl"], [dir="rtl"] *) {
  --tw-rotate: 180deg;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

.rtl\\:flex-row-reverse:where([dir="rtl"], [dir="rtl"] *) {
  flex-direction: row-reverse;
}

.rtl\\:space-x-reverse:where([dir="rtl"], [dir="rtl"] *) > :not([hidden]) ~ :not([hidden]) {
  --tw-space-x-reverse: 1;
}

.rtl\\:divide-x-reverse:where([dir="rtl"], [dir="rtl"] *) > :not([hidden]) ~ :not([hidden]) {
  --tw-divide-x-reverse: 1;
}

@media (min-width: 1024px) {

  .rtl\\:lg\\:-translate-x-0:where([dir="rtl"], [dir="rtl"] *) {
    --tw-translate-x: -0px;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }

  .rtl\\:lg\\:translate-x-full:where([dir="rtl"], [dir="rtl"] *) {
    --tw-translate-x: 100%;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  }
}

.\\[\\&\\.trix-active\\]\\:bg-gray-50.trix-active {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-50), var(--tw-bg-opacity));
}

.\\[\\&\\.trix-active\\]\\:text-primary-600.trix-active {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-600), var(--tw-text-opacity));
}

.dark\\:\\[\\&\\.trix-active\\]\\:bg-white\\/5.trix-active:is(.dark *) {
  background-color: rgb(255 255 255 / 0.05);
}

.dark\\:\\[\\&\\.trix-active\\]\\:text-primary-400.trix-active:is(.dark *) {
  --tw-text-opacity: 1;
  color: rgba(var(--primary-400), var(--tw-text-opacity));
}

.\\[\\&\\:\\:-ms-reveal\\]\\:hidden::-ms-reveal {
  display: none;
}

.\\[\\&\\:not\\(\\:first-of-type\\)\\]\\:border-s:not(:first-of-type) {
  border-inline-start-width: 1px;
}

.\\[\\&\\:not\\(\\:has\\(\\.fi-ac-action\\:focus\\)\\)\\]\\:focus-within\\:ring-2:focus-within:not(:has(.fi-ac-action:focus)) {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.\\[\\&\\:not\\(\\:has\\(\\.fi-ac-action\\:focus\\)\\)\\]\\:focus-within\\:ring-danger-600:focus-within:not(:has(.fi-ac-action:focus)) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-600), var(--tw-ring-opacity));
}

.\\[\\&\\:not\\(\\:has\\(\\.fi-ac-action\\:focus\\)\\)\\]\\:focus-within\\:ring-primary-600:focus-within:not(:has(.fi-ac-action:focus)) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-600), var(--tw-ring-opacity));
}

.dark\\:\\[\\&\\:not\\(\\:has\\(\\.fi-ac-action\\:focus\\)\\)\\]\\:focus-within\\:ring-danger-500:focus-within:not(:has(.fi-ac-action:focus)):is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--danger-500), var(--tw-ring-opacity));
}

.dark\\:\\[\\&\\:not\\(\\:has\\(\\.fi-ac-action\\:focus\\)\\)\\]\\:focus-within\\:ring-primary-500:focus-within:not(:has(.fi-ac-action:focus)):is(.dark *) {
  --tw-ring-opacity: 1;
  --tw-ring-color: rgba(var(--primary-500), var(--tw-ring-opacity));
}

.\\[\\&\\:not\\(\\:last-of-type\\)\\]\\:border-e:not(:last-of-type) {
  border-inline-end-width: 1px;
}

.\\[\\&\\:not\\(\\:nth-child\\(1_of_\\.fi-btn\\)\\)\\]\\:shadow-\\[-1px_0_0_0_theme\\(colors\\.gray\\.200\\)\\]:not(:nth-child(1 of .fi-btn)) {
  --tw-shadow: -1px 0 0 0 rgba(var(--gray-200), 1);
  --tw-shadow-colored: -1px 0 0 0 var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.dark\\:\\[\\&\\:not\\(\\:nth-child\\(1_of_\\.fi-btn\\)\\)\\]\\:shadow-\\[-1px_0_0_0_theme\\(colors\\.white\\/20\\%\\)\\]:not(:nth-child(1 of .fi-btn)):is(.dark *) {
  --tw-shadow: -1px 0 0 0 rgb(255 255 255 / 20%);
  --tw-shadow-colored: -1px 0 0 0 var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}

.\\[\\&\\:not\\(\\:nth-last-child\\(1_of_\\.fi-btn\\)\\)\\]\\:me-px:not(:nth-last-child(1 of .fi-btn)) {
  margin-inline-end: 1px;
}

.\\[\\&\\:nth-child\\(1_of_\\.fi-btn\\)\\]\\:rounded-s-lg:nth-child(1 of .fi-btn) {
  border-start-start-radius: 0.5rem;
  border-end-start-radius: 0.5rem;
}

.\\[\\&\\:nth-last-child\\(1_of_\\.fi-btn\\)\\]\\:rounded-e-lg:nth-last-child(1 of .fi-btn) {
  border-start-end-radius: 0.5rem;
  border-end-end-radius: 0.5rem;
}

.\\[\\&\\>\\*\\:first-child\\]\\:relative>*:first-child {
  position: relative;
}

.\\[\\&\\>\\*\\:first-child\\]\\:mt-0>*:first-child {
  margin-top: 0px;
}

.\\[\\&\\>\\*\\:first-child\\]\\:before\\:absolute>*:first-child::before {
  content: var(--tw-content);
  position: absolute;
}

.\\[\\&\\>\\*\\:first-child\\]\\:before\\:inset-y-0>*:first-child::before {
  content: var(--tw-content);
  top: 0px;
  bottom: 0px;
}

.\\[\\&\\>\\*\\:first-child\\]\\:before\\:start-0>*:first-child::before {
  content: var(--tw-content);
  inset-inline-start: 0px;
}

.\\[\\&\\>\\*\\:first-child\\]\\:before\\:w-0\\.5>*:first-child::before {
  content: var(--tw-content);
  width: 0.125rem;
}

.\\[\\&\\>\\*\\:first-child\\]\\:before\\:bg-primary-600>*:first-child::before {
  content: var(--tw-content);
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-600), var(--tw-bg-opacity));
}

.\\[\\&\\>\\*\\:first-child\\]\\:dark\\:before\\:bg-primary-500:is(.dark *)>*:first-child::before {
  content: var(--tw-content);
  --tw-bg-opacity: 1;
  background-color: rgba(var(--primary-500), var(--tw-bg-opacity));
}

.\\[\\&\\>\\*\\:last-child\\]\\:mb-0>*:last-child {
  margin-bottom: 0px;
}

.\\[\\&_\\.choices\\\\_\\\\_inner\\]\\:ps-0 .choices__inner {
  padding-inline-start: 0px;
}

.\\[\\&_\\.fi-badge-delete-button\\]\\:hidden .fi-badge-delete-button {
  display: none;
}

.\\[\\&_\\.filepond--root\\]\\:font-sans .filepond--root {
  font-family: Figtree, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.\\[\\&_optgroup\\]\\:bg-white optgroup {
  --tw-bg-opacity: 1;
  background-color: rgb(255 255 255 / var(--tw-bg-opacity));
}

.\\[\\&_optgroup\\]\\:dark\\:bg-gray-900:is(.dark *) optgroup {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

.\\[\\&_option\\]\\:bg-white option {
  --tw-bg-opacity: 1;
  background-color: rgb(255 255 255 / var(--tw-bg-opacity));
}

.\\[\\&_option\\]\\:dark\\:bg-gray-900:is(.dark *) option {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
}

:checked+*>.\\[\\:checked\\+\\*\\>\\&\\]\\:text-white {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

@media(hover:hover) {

  .\\[\\@media\\(hover\\:hover\\)\\]\\:transition {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-backdrop-filter;
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
  }

  .\\[\\@media\\(hover\\:hover\\)\\]\\:duration-75 {
    transition-duration: 75ms;
  }
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:bg-custom-600 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-600), var(--tw-bg-opacity));
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:bg-gray-400 {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-400), var(--tw-bg-opacity));
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:text-white {
  --tw-text-opacity: 1;
  color: rgb(255 255 255 / var(--tw-text-opacity));
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:ring-0 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(0px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:hover\\:bg-custom-500:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-500), var(--tw-bg-opacity));
}

input:checked+.\\[input\\:checked\\+\\&\\]\\:hover\\:bg-gray-300:hover {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-300), var(--tw-bg-opacity));
}

input:checked+.dark\\:\\[input\\:checked\\+\\&\\]\\:bg-custom-500:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-500), var(--tw-bg-opacity));
}

input:checked+.dark\\:\\[input\\:checked\\+\\&\\]\\:bg-gray-600:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-600), var(--tw-bg-opacity));
}

input:checked+.dark\\:\\[input\\:checked\\+\\&\\]\\:hover\\:bg-custom-400:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--c-400), var(--tw-bg-opacity));
}

input:checked+.dark\\:\\[input\\:checked\\+\\&\\]\\:hover\\:bg-gray-500:hover:is(.dark *) {
  --tw-bg-opacity: 1;
  background-color: rgba(var(--gray-500), var(--tw-bg-opacity));
}

input:checked:focus-visible+.\\[input\\:checked\\:focus-visible\\+\\&\\]\\:ring-custom-500\\/50 {
  --tw-ring-color: rgba(var(--c-500), 0.5);
}

input:checked:focus-visible+.dark\\:\\[input\\:checked\\:focus-visible\\+\\&\\]\\:ring-custom-400\\/50:is(.dark *) {
  --tw-ring-color: rgba(var(--c-400), 0.5);
}

input:focus-visible+.\\[input\\:focus-visible\\+\\&\\]\\:z-10 {
  z-index: 10;
}

input:focus-visible+.\\[input\\:focus-visible\\+\\&\\]\\:ring-2 {
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

input:focus-visible+.\\[input\\:focus-visible\\+\\&\\]\\:ring-gray-950\\/10 {
  --tw-ring-color: rgba(var(--gray-950), 0.1);
}

input:focus-visible+.dark\\:\\[input\\:focus-visible\\+\\&\\]\\:ring-white\\/20:is(.dark *) {
  --tw-ring-color: rgb(255 255 255 / 0.2);
}

`;
  window.__ENJOY_HEN_ORIGIN__ = window.__ENJOY_HEN_ORIGIN__ || (() => {
    var _a;
    try {
      const scriptUrl = ((_a = document.currentScript) == null ? void 0 : _a.src) || (typeof document === "undefined" && typeof location === "undefined" ? require("url").pathToFileURL(__filename).href : typeof document === "undefined" ? location.href : _documentCurrentScript && _documentCurrentScript.src || new URL("enjoyHen.standalone.js", document.baseURI).href);
      return new URL(scriptUrl).origin;
    } catch {
      return window.location.origin;
    }
  })();
  const componentWithProps = {
    ...EnjoyHen,
    props: {
      teamSlug: {
        type: String,
        required: false,
        default: () => {
          return window.location.pathname.split("/").pop();
        }
      },
      heygenApiKey: {
        type: String,
        default: ""
      },
      heygenServerUrl: {
        type: String,
        default: "https://api.heygen.com"
      },
      locale: {
        type: String,
        default: "it-IT"
      },
      teamLogo: {
        type: String,
        default: "/images/logoai.jpeg"
      }
    },
    // Inietta gli stili Tailwind nel Shadow DOM del custom element
    styles: [cssText]
  };
  const EnjoyHenElement = /* @__PURE__ */ defineCustomElement(componentWithProps);
  customElements.define("enjoy-hen", EnjoyHenElement);
  return EnjoyHenElement;
});
