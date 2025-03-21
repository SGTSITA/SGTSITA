/**
 * Fileuploader *** Trial ***
 * Copyright (c) 2018 Innostudio.de
 * Website: http://innostudio.de/fileuploader/
 * Version: 2.2 (01-Apr-2019)
 * Requires: multer, mime-types and gm
 * License: https://innostudio.de/fileuploader/documentation/#license
 */
const multer = require('multer'),
    mime = require('mime-types'),
    fs = require('fs'),
    gm = require('gm'),
    emptyFn = function() {},
    error_messages = {
        EMPTY_FIELD: 'No file was choosed. Please select one',
        MAX_FILES_NUMBER: 'Maximum number of files is exceeded',
        INVALID_TYPE: 'File type is not allowed for {file_name}',
        MAX_SIZE: 'Files are too large',
        MAX_FILE_SIZE: '{file_name} is too large'
    };
var Fileuploader = function(c, f, g, h) {
    return this.req = g, this.res = h, this.options = extendDefaults.call(this, f, {
        limit: null,
        maxSize: null,
        fileMaxSize: null,
        extensions: null,
        required: !1,
        uploadDir: 'uploads/',
        title: ['auto', 12],
        replace: !1,
        listInput: !0,
        files: [],
        move_uploaded_file: function(l) {
            return fs.renameSync(l.tmp, l.file), !0
        },
        validate_file: null
    }), this.multer = multer({
        dest: this.options.uploadDir,
        fileFilter: fileFilter
    }), this.field = {
        name: c,
        input: [],
        listInput: null
    }, this
};
Fileuploader.prototype.getFileList = function(c) {
    var f = [],
        g = this.options.files;
    return isset(c) ? g.forEach(function(h) {
        var l = getFileAttribute(h, c);
        f.push(l ? l : h.file)
    }) : f = g, f
}, Fileuploader.prototype.getUploadedFiles = function() {
    return this.options.files.filter(c => isset(c.uploaded))
}, Fileuploader.prototype.getPreloadedFiles = function() {
    return this.options.files.filter(c => !isset(c.uploaded))
}, Fileuploader.prototype.getRemovedFiles = function(c = 'file') {
    var f = [],
        g = this.options.files,
        h = this.field.listInput;
    return null != h && g.forEach(function(j, l, m) {
        -1 != h.list.indexOf(getFileAttribute(j, c)) || isset(j.uploaded) || (f.push(j), m.splice(l, 1))
    }), f
}, Fileuploader.prototype.getListInput = function() {
    return this.field.listInput
}, Fileuploader.prototype.generateInput = function() {
    var c = [],
        f = Object.assign({}, this.options, {
            name: this.field.name
        });
    for (var g in f) {
        var h = f[g],
            j = 'data-fileuploader-' + g;
        h && ('limit' == g || 'maxSize' == g || 'fileMaxSize' == g ? c.push({
            key: j,
            value: h
        }) : 'listInput' == g ? c.push({
            key: j,
            value: 'boolean' == typeof h ? JSON.stringify(h) : h
        }) : 'extensions' == g ? c.push({
            key: j,
            value: h.join(',')
        }) : 'name' == g ? c.push({
            key: g,
            value: h
        }) : 'required' == g ? c.push({
            key: g,
            value: ''
        }) : 'files' == g ? c.push({
            key: j,
            value: JSON.stringify(h)
        }) : void 0)
    }
    return '<input type="file" ' + c.map(l => l.key + '=\'' + l.value.replace(/\'/g, '"') + '\'').join(' ') + '>'
}, Fileuploader.prototype.mimeContentType = function(c) {
    return mime.lookup(c)
}, Fileuploader.prototype.upload = function(c) {
    var f = this,
        g = 1 === f.options.limit ? 'single' : 'array',
        h = {
            hasWarnings: !1,
            isSuccess: !0,
            warnings: [],
            files: [],
            _callback: c,
            _setStatus: function(j, l, m) {
                var n = this,
                    o = n._callback;
                if (null !== j && (!0 === j ? n.isSuccess = !0 : (n.isSuccess = !1, n.hasWarnings = !0, l && n.warnings.push(l))), m && 'function' == typeof o) {
                    var p = !1;
                    if (f.options.files.forEach(function(q) {
                            isset(q._processing) && (q._processingCallback = function() {
                                delete q._processing, delete q._processingCallback, n._setStatus(null, null, !0)
                            }, p = !0)
                        }), p) return;
                    delete n._callback, delete n._setStatus, o(n)
                }
            }
        };
    f.multer[g](f.field.name)(f.req, f.res, function(j) {
        if (j) return h._setStatus(!1, {
            code: j.code,
            message: j.message
        }, !0);
        var l = 'single' == g ? [f.req.file] : f.req.files,
            m = f.req.body,
            n = isset(m) && isset(m._chunkedd) && 1 == l.length && isJson(m._chunkedd) && JSON.parse(m._chunkedd),
            o = validate_files.call(f, l);
        f.field.input = l.length, f.field.listInput = getListInputFiles.call(f, f.field.name), !0 === o ? handleUpload.call(f, l, h) : h._setStatus(!1, {
            code: o,
            message: error_messages[o]
        }, !0), l.forEach(function(p) {
            unlinkTmp(p)
        })
    })
};

function handleUpload(c, f) {
    for (var g = this, h = g.options, j = g.field.listInput, l = 0; l < c.length; l++) {
        var m = {
                name: c[l].originalname,
                tmp: c[l].path,
                tmp_name: c[l].filename,
                type: c[l].mimetype,
                size: c[l].size
            },
            n = '0:/' + m.name,
            o = null === j || -1 < j.list.indexOf(n),
            p = pathinfo(m.name),
            q = new Date;
        m.oldname = m.name, m.oldtitle = p.title, m.extension = p.extension, m.title = m.oldtitle, m.size2 = formatSize(m.size), m.name = generateFilename(h, m), m.title = pathinfo(m.name).title, m.file = h.uploadDir + m.name, m.replaced = fs.existsSync(g.options.uploadDir + m.name), m.date = q;
        var r = validate_files.call(g, m);
        if (!0 !== r) {
            if (!o) continue;
            f.files = [], f._setStatus(!1, {
                code: r,
                message: parseVariables(error_messages[r] || r, m)
            });
            break
        } else if (o) {
            var s = 0;
            j && (s = j.list.indexOf(n), m.listProps = j.values[s], j.list.splice(s, 1), j.values.splice(s, 1)), f.files.push(m)
        }
    }
    return f.hasWarnings || f.files.forEach(function(t, u) {
        h.move_uploaded_file(t) ? (t.uploaded = !0, delete t.chunked, delete t.tmp, delete t.tmp_name, h.files.push(t)) : f.files.splice(u, 1)
    }), j && h.files.forEach(function(t) {
        if (!isset(t.listProps)) {
            var w = j.list.indexOf(t.file); - 1 < w && (t.listProps = j.values[w])
        }
        isset(t.listProps) && (delete t.listProps.file, 0 == t.listProps.length && delete t.listProps)
    }), f._setStatus(null, null, !0)
}

function validate_files(c) {
    if (c instanceof Array) {
        if (this.options.required && 0 == c.length + this.options.files.length) return 'EMPTY_FIELD';
        if (this.options.limit && c.length + this.options.files.length > this.options.limit) return 'MAX_FILES_NUMBER';
        if (this.options.maxSize && (c.reduce((g, h) => g + h.size, 0) + this.options.files.reduce((g, h) => g + h.size, 0)) / 1e6 > this.options.maxSize) return 'MAX_SIZE'
    } else {
        if (this.options.extensions && -1 == this.options.extensions.indexOf(c.extension) && -1 == this.options.extensions.indexOf(c.type)) return 'INVALID_TYPE';
        if (this.options.fileMaxSize && c.size / 1e6 > this.options.fileMaxSize) return 'MAX_FILE_SIZE';
        var f = 'function' != typeof this.options.validate_file || this.options.validate_file(file, this.options);
        if (!0 !== f) return f
    }
    return !0
}

function extendDefaults(c, f) {
    var g = this,
        h = Object.assign({}, f, c || {});
    return h.files.forEach(function(j) {
        j.type || (j.type = g.mimeContentType(j.relative_path || j.file)), j.appended = !0
    }), h
}

function fileFilter(c, f, g) {
    g(null, !0)
}

function getFileAttribute(c, f) {
    var g = null;
    return isset(c.data) && isset(c.data[f]) && (g = c.data[f]), isset(c[f]) && (g = c[f]), g
}

function generateFilename(c, f) {
    return filterFilename(f.name)
}

function getListInputFiles(c) {
    var f = 'fileuploader-list-' + c,
        g = this.req.body,
        h = null;
    if ('string' == typeof this.options.listInput && (f = this.options.listInput), g && g[f] && isJson(g[f])) {
        var j = {
            list: [],
            values: JSON.parse(g[f])
        };
        j.values.forEach(function(l) {
            j.list.push(l.file)
        }), h = j
    }
    return h
}

function parseVariables(c, f) {
    return c += '', c = c.replace(/\{file_name\}/g, f.name), c = c.replace(/\{file_size\}/g, f.size), c = c.replace(/\{extension\}/g, f.extension), c
}

function random_string(c) {
    for (var f = '_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', g = '', h = 0; h < c; h++) g += f.charAt(Math.floor(Math.random() * f.length));
    return g
}

function pathinfo(c) {
    var f = c.substr(0, c.lastIndexOf('/')),
        g = -1 == c.indexOf('.') ? '' : c.split('.').pop().toLowerCase(),
        h = c.substr(f.length + (empty(f) ? 0 : 1), c.length - g.length - (empty(g) ? 0 : 1));
    return {
        path: f,
        title: h,
        extension: g
    }
}

function filterFilename(c) {
    var f = '_',
        g = /["<>#%\{\}\|\\\^~\[\]`;\?:@=&\*]/g;
    return c = c.replace(g, f), c = c.replace(/_{2,}/g, f), c
}

function formatSize(c) {
    if (0 == c) return '0 Byte';
    var f = 1e3,
        h = Math.floor(Math.log(c) / Math.log(f));
    return (c / Math.pow(f, h)).toPrecision(3) + ' ' + ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][h]
}

function unlinkTmp(c) {
    fs.unlink(c.tmp || c.path, emptyFn)
}

function isJson(c) {
    try {
        JSON.parse(c)
    } catch (f) {
        return !1
    }
    return !0
}

function isset(c) {
    return 'undefined' != typeof c
}

function empty(c) {
    return 0 === (c + '').length
}
module.exports = function() {
    return new Fileuploader(...arguments)
};