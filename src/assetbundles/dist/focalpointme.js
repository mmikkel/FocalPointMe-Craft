/** global: Craft */
/** global: Garnish */
/**
 * Asset index class
 */

(() => {

    if (!window.$ || !window.Craft || !Craft.ElementEditor) {
        return;
    }

    Craft.FocalPointMe = Garnish.Base.extend({

        init(elementEditor) {

            this.elementEditor = elementEditor;

            const $thumb = this.elementEditor.$container.find('.preview-thumb-container .edit-btn')
                .closest('.preview-thumb-container')
                .find('img')
                .eq(0);

            if (!$thumb.length) {
                return;
            }

            // Get the asset ID
            const assetId = parseInt(elementEditor.settings.canonicalId, 10);

            // Get the current focal point, which should be saved to an HTML comment in the DOM
            const key = `fp:${assetId}:`;
            const iterator = document.createNodeIterator(elementEditor.$container.get(0), NodeFilter.SHOW_COMMENT, () => NodeFilter.FILTER_ACCEPT);

            let focalPoint = {
                x: 0.5,
                y: 0.5
            };
            
            while (node = iterator.nextNode()) {
                const value = node.nodeValue.toString()
                    .trim();
                if (value.startsWith(key)) {
                    try {
                        focalPoint = JSON.parse(value.split(key)
                            .pop());
                    } catch (error) {
                        console.error(error);
                    }
                    break;
                }
            }

            $thumb
                .attr({ draggable: 'false' })
                .wrap('<div style="position:relative;z-index:1;" />')
                .css({ zIndex: 1 });

            this.$fpEditor = $('<div class="focalpointme-editor" />');

            $thumb.parent()
                .append(this.$fpEditor);

            const $container = this.$fpEditor.closest('.preview-thumb-container');
            $container.addClass('focalpointme');

            this.$focalPoint = $(`<button type="button" class="focalpointme-point" style="left:${focalPoint.x * 100}%;top:${focalPoint.y * 100}%;"><span /></button>`);

            this.$fpEditor.append(this.$focalPoint);

            this.$fpEditor.on('click', ({ originalEvent: e }) => {
                if (this.isFocalPointDragging) {
                    return;
                }
                this.setFocalPointPosition(e.pageX, e.pageY);
            });

            this.$fpEditor.on('mouseenter', () => {
                $container.addClass('hide-buttons');
            });

            this.$fpEditor.on('mouseleave', () => {
                $container.removeClass('hide-buttons');
                this.isFocalPointDragging = false;
            });

            this.$focalPoint.on('mousedown', () => {
                this.isFocalPointDragging = true;
            });

            this.$focalPoint.on('mouseup', () => {
                this.isFocalPointDragging = false;
            });

            $(window)
                .on('mousemove', ({ originalEvent: e }) => {
                    if (!this.isFocalPointDragging) {
                        return;
                    }
                    this.setFocalPointPosition(e.pageX, e.pageY);
                });

            // Update the focal point in the thumbnail if the image is edited via the image editor
            Garnish.on(Craft.AssetImageEditor, 'save', ({ target: imageEditor }) => {
                if (parseInt(imageEditor.assetId, 10) !== assetId) {
                    return;
                }
                Craft.sendActionRequest('POST', 'focal-point-me/default/get-focal-point', { data: { assetId } })
                    .then(({ status, data }) => {
                        if (status !== 200 || !data.focalPoint) {
                            throw new Error();
                        }
                        const { x, y } = data.focalPoint;
                        this.$focalPoint.css({
                            left: `${x * 100}%`,
                            top: `${y * 100}%`
                        });
                        // This essentially "resets" the thing because the asset was just saved via the image editor
                        if (this.$focalPointInput) {
                            this.$focalPointInput.remove();
                            this.$focalPointInput = null;
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });

        },

        setFocalPointPosition(pageX, pageY) {

            const parentOffset = this.$fpEditor.offset();
            const imageWidth = this.$fpEditor.outerWidth();
            const imageHeight = this.$fpEditor.outerHeight();
            const posX = pageX - parentOffset.left;
            const posY = pageY - parentOffset.top;
            const precision = Math.pow(10, 2);

            let percentX = Math.round((posX / imageWidth) * 100 * precision) / precision;
            let percentY = Math.round((posY / imageHeight) * 100 * precision) / precision;

            percentX = Math.max(0, Math.min(percentX, 100));
            percentY = Math.max(0, Math.min(percentY, 100));

            this.$focalPoint.css({
                left: percentX + '%',
                top: percentY + '%'
            });

            // Update the input field
            const focalPointX = parseFloat((percentX / 100).toFixed(4));
            const focalPointY = parseFloat((percentY / 100).toFixed(4));

            if (!this.$focalPointInput) {
                // Create an hidden input field to save the focal point
                let inputName;
                if (this.elementEditor.namespace) {
                    inputName = `${this.elementEditor.namespace}[focalPoint]`;
                } else {
                    inputName = 'focalPoint';
                }
                this.$focalPointInput = $(`<input type="hidden" name="${inputName}" />`);
                this.elementEditor.$container.append(this.$focalPointInput);
            }

            this.$focalPointInput.val(`${focalPointX};${focalPointY}`);

        },

        elementEditor: null,
        isFocalPointDragging: false,
        $focalPoint: null,
        $focalPointInput: null

    });

    // Hook into the unified element editor and boot up FocalPointMe if this is an asset
    const fn = Craft.ElementEditor.prototype.init;
    Craft.ElementEditor.prototype.init = function () {
        fn.apply(this, arguments);
        if (this.settings.elementType !== 'craft\\elements\\Asset' || !!this.$container.data('focalPointMe')) {
            return;
        }
        this.$container.data('focalPointMe', new Craft.FocalPointMe(this));
    };

})();
