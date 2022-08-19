/*
|--------------------------------------------------------------------------
| Comments
|--------------------------------------------------------------------------
|
*/
if (typeof myapp === 'undefined') {
    myapp = {}
}

    myapp.comment = {

        /**
         * Init
         */
        init: function() {

            let scope = myapp.comment


            // Register events
            $(function() {


                /**
                 * Post a new comment
                 * 
                 * @el .comment-list button[name="add"]
                 */ 
                $(document).on('click', '.comment-list button[name="add"]', function(e) {

                    const _btn = this,
                          _comment_wrap = $(this).parents('.new')

                    // prevent empty submission   
                    if( myapp.messageHelper.validateEmpty(_comment_wrap.find('textarea')) == false ) {
                        return
                    }

                    // set the button
                    _btn.classList.add("loading")
                    _btn.disabled = true

                    let comment = myapp.form.collectInputs(_comment_wrap)
                        comment.id = 0

                        comment.type = 1 // Comment

                        if(comment.parent_comment_id) {
                            comment.type = 2 // Reply
                        }

                    // save
                    let response = scope.save(comment)

                    response.then(function (response) {

                        const injectHTML = myapp.templates.comment.render([response]),
                              today_title = $('.comment-list > .today')
                        
                        if(today_title.length == 1) {
                            today_title.replaceWith(injectHTML)
                        }
                        else {
                            $('.comment-list > .new').after(injectHTML)
                        }

                        // clear the form
                        myapp.messageHelper.clearForm(_comment_wrap.find('textarea'))

                        // set the button
                        _btn.classList.remove("loading")
                        _btn.disabled = false

                    })
                    .catch(e => _errorResponse(e))

                    e.preventDefault()
                    return false
                })
    


    
                /**
                 * Edit a comment or reply, open a new comment editor in tippy
                 * 
                 * @el .comment button[name="edit"]
                 */
                $(document).on('click', '.comment button[name="edit"]', function() {
    
                    const _comment_wrap = $(this).parents('.comment'),
                             comment_id = this.value
 
                    axios.get(`/admin/comments/get/${comment_id}`)
                        .then(function (response) {
                        
                            if(!response.data) return false

                            // is this a reply?
                            const is_reply = response.data.type == 2 ? true : false

                            content = is_reply ? myapp.templates.comment.renderReplyEditor(response.data.id, 0) : myapp.templates.comment.renderEditor(response.data) 
                            content = $.parseHTML(content)

                            const textarea = $(content).find('textarea')
                            const MDE_editor = myapp.markup.initSimpleMDE(textarea[0], is_reply),
                                          cm = MDE_editor.codemirror

                            // Fix Mentions
                            cm.getDoc().setValue(response.data.comment)
                            
                            // _ init tippy
                            let tippyEl = tippy(`.comment-list [data-id="${comment_id}"]`, {
                                content: $(content)[0],
                                allowHTML: true,
                                interactive: true,
                                appendTo: 'parent',
                                delay: [0, 500],
                                trigger: 'click',
                                theme: 'light',
                                animation: 'shift-toward-subtle',
                                showOnCreate: true,
                                hideOnClick: 'toggle',
                                maxWidth: '450',
                                zIndex: 9,
                                onHidden(a) {
                                    a.destroy()
                                    //
                                    _comment_wrap.removeClass("active")
                                },
                                onShow(a) {
                                    //tippy.hideAll({duration: 0.5})
                                    hideAllTippy({duration: 0.5})

                                    //
                                    _comment_wrap.addClass("active")
                                }
                            })  

                            // Set focus
                            setTimeout (() => {
                                cm.focus();
                                cm.setCursor(cm.lineCount(), 0)
                            }, 50)

                            // Register edit saving btn onClick event
                            $(content).find('button[name="edit"]').on('click', function(e) {
                                myapp.comment.updateComment(this, tippyEl)
                            })
                            return

                        })
                        .catch(e => _errorResponse(e))
                    return
                })
    



                /**
                 * Reply to a comment, open a new simplified comment editor in tippy
                 * 
                 */
                $(document).on('click', '.comment button[name="reply"]', function() {

                    const _comment_wrap = $(this).parents('.comment'),
                             parent_comment_id = _comment_wrap.data('id')

                    let content = myapp.templates.comment.renderReplyEditor(0, parent_comment_id)
                        content = $.parseHTML(content)

                    const textarea = $(content).find('textarea')

                    const MDE_editor = myapp.markup.initSimpleMDE(textarea[0], true),
                                  cm = MDE_editor.codemirror
                 
                    let tippyEl = tippy(`[data-id="${parent_comment_id}"].comment`, {
                        content: $(content)[0],
                        allowHTML: true,
                        interactive: true,
                        appendTo: 'parent',
                        delay: [0, 500],
                        trigger: 'click',
                        theme: 'light',
                        animation: 'shift-toward-subtle',
                        showOnCreate: true,
                        hideOnClick: 'toggle',
                        maxWidth: '450',
                        zIndex: 9,
                        onHidden(a) {
                            a.destroy()
                            //
                            _comment_wrap.removeClass("active")
                        },
                        onShow(a) {
                            //tippy.hideAll({duration: 0.5})
                            hideAllTippy({duration: 0.5})
                            //
                            _comment_wrap.addClass("active")
                        }
                    })  

                    // Set focus
                    setTimeout (() => {
                        cm.focus();
                        cm.setCursor(1, 1)
                    }, 50)

                    // Register edit btn onClick event
                    $(content).find('button[name="edit"]').on('click', function(e) {
                        myapp.comment.updateComment(this, tippyEl)
                    })
                    return
                })

    

                /**
                 * Delete a comment onClick event
                 * 
                 * @el .comment button[name="delete"]
                 */
                $(document).on('click', '.comment button[name="delete"]', function() {

                    const _comment_wrap = $(this).closest('[data-id]')
                             comment_id = this.value

                    // delete the comment
                    scope.delete(_comment_wrap, comment_id || 0)
                })
               
            }) // -- doc. ready end

        },




        /**
         * Save a comment 
         * 
         * @param {Object} comment
         * @param {Boolean} edit 
         * 
         * @return {Promise} Comment
         */
        save: async function(comment, edit = false) {

            if(!comment) {
                return false
            }

            let url_parts = window.location.pathname.split('/')
                url_parts = removeArrayItem(url_parts, "")
                url_parts = removeArrayItem(url_parts, "admin")

            comment.data_type = url_parts[0],
            comment.data_id = url_parts[1]


            try {
                // fetch data from a url endpoint
                const response = await axios.post('/admin/comments/store', comment)
                return response.data
            } 
            catch (error) {
                console.log(error)
            }
            return false
        },



        /**
         * Delete a comment
         * 
         * @param {Element} comment
         * @param {int} comment_id
         * 
         * @return Boolean
         */
        delete: (el, comment_id) => {
 
            if( !comment_id ) {
                return false
            }

            axios.post('/admin/comments/delete', {
                comment_id: comment_id
            })
            .then(function (response) {

                // remove the title
                if(el.prev('.comment-feed-date').length && !el.next('.comment').length) {
                    removeElement(el.prev('.comment-feed-date')[0])
                }

                // remove comment
                removeElement(el[0])
            
                return true
            })
            .catch(e => _errorResponse(e))

            return false
        },




        /**
         *  Store comment changes, reply changes, new replies
         * 
         * @param {el} el 
         * @param {tippy instance} tippy 
         */
        updateComment: function(el, tippyEl) {

            const _btn = el,
                  _comment_wrap = $(el).parents('.edit')
    
            if( myapp.messageHelper.validateEmpty(_comment_wrap.find('textarea')) == false ) {
                return
            }

            // set button
            _btn.classList.add("loading")
            _btn.disabled = true

            let comment = myapp.form.collectInputs(_comment_wrap)

                comment.type = 1 // Comment

                if(comment.parent_comment_id) {
                    comment.type = 2 // Reply
                }

            // save the comment
            let response = myapp.comment.save(comment)

            response.then(function (response) {

                if(!response) {
                    return false
                }

                // Get the rendered UI. (for reply or comment)
                let injectHTML = response.type == 2 ? myapp.templates.comment.reply([response]) : myapp.templates.comment.render([response], false)

                // Comment or reply update
                if(comment.id > 0) {
                    $(`.comment-list [data-id="${comment.id}"]`).replaceWith(injectHTML)
                }
                // New reply
                else {
                    // todo: order
                    $(`.comment-list [data-id="${comment.parent_comment_id}"] > .comment--body`).after(injectHTML)
                }

                tippyEl[0].hide()

                // set button
                _btn.classList.remove("loading")
                _btn.disabled = false

                _comment_wrap.removeClass("active")
                return
            })
            .catch(e => _errorResponse(e))
            return false
        },



        /**
         * Load more Comments on page scroll.
         * 
         * @param data Object with Query offset and limit
         * 
         * @return fn
         */
        loadMoreCommentsInit: (data) => {

            const activitiesTab = document.getElementById("admin-comments")

            if(!activitiesTab) {
                return
            }

            const Tab = document.getElementById("admin-comments"),
                  commentList = Tab.querySelectorAll(".comment-list")[0]

            const observer = new IntersectionObserver((el, observer) => {
                if(el[0].isIntersecting === true) {
                    myapp.comment.loadMoreComments(commentList, data)
                }
            })

            const elToObserve = Tab.querySelector('.comment-list .comment:last-child')

            if(!elToObserve) 
                return 

            observer.observe(Tab.querySelector('.comment-list .comment:last-child'))
        },




        /**
         * Load more comment
         * 
         * @param (el) comment list
         * 
         * @return fn
         */
        loadMoreComments(commentList, data) {

            console.log('todo: get data_type and data_id')
            
            axios.get(`/${window.history.state.data_type}/api/get/${window.history.state.data_id}/comments`, {
                params: {
                    offset: data.offset,
                    limit: data.limit
                }
            })
            .then(function (response) {
            
                if(!response.data || response.data.length == 0) {
                    return false
                }

                // send data to template
                let injectHTML = myapp.templates.comment.render(response.data)
        
                // insert the HTML template content
                commentList.insertAdjacentHTML('beforeend', injectHTML)
                
                // init the new scroll function
                myapp.comment.loadMoreCommentsInit({
                    offset: data.offset + data.limit,
                    limit: data.limit
                })

                myapp.tippy.init(document.getElementById("admin-comments"))

                return false
            })
            .catch(error => _errorResponse(error))
        }



    }


    myapp.comment.init()