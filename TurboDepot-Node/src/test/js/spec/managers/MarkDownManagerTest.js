'use strict';


/**
 * MarkDownManager tests
 */

const path = require('path');
const { FilesManager } = require(path.resolve('target/turbodepot-node/dist/ts/index'));
const { MarkDownManager } = require(path.resolve('target/turbodepot-node/dist/ts/index'));


describe('MarkDownManager', function() {
    
    
    beforeEach(function() {
        
        this.filesManager = new FilesManager();
        this.sut = new MarkDownManager();
    });
    
    
    it('should have a correctly implemented construct method', function() {

        // TODO
    });
    
    
    it('should have a correctly implemented validate method', function() {

        // TODO
    });
    
    
    it('should have a correctly implemented isValid method', function() {

        // TODO
    });
    
    
    it('should have a correctly implemented testToHtml method', function() {

        // Test empty values
        expect(() => { this.sut.toHtml(null); }).toThrowError(Error, /string must be a string/);
        expect(this.sut.toHtml('')).toBe('');
        expect(this.sut.toHtml('      ')).toBe('');
        expect(this.sut.toHtml("\n\n\n\n")).toBe('');
        
        // Test ok values
        expect(this.sut.toHtml('hello')).toBe('<p>hello</p>');
        expect(this.sut.toHtml('# hello')).toBe('<h1>hello</h1>');
        expect(this.sut.toHtml('## hello')).toBe('<h2>hello</h2>');
        // TODO - more cases

        // Test wrong values
        // Test exceptions
        // TODO
    });
});