const del = require('del');
const config = require('../config.js');

module.exports = function() {
    return del(config.built.dir);
};