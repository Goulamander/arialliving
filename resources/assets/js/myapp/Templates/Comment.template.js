
/*
|--------------------------------------------------------------------------
| Comment templates
|--------------------------------------------------------------------------
*/

if (typeof myapp.templates === 'undefined') {
    myapp.templates = {}
}


myapp.templates.comment = {


    /**
     * Render Comments list or a single comment
     * 
     * @param {array} comments - Comments Object array
     * @param {bool} is_header
     */
    render(comments, is_header = true) {

        comments = Object.entries(comments)

        if(!comments.length) {
            return ''
        }

        let m = '',
        comment_list = '' 

       
        for (const [indx, item] of comments) {

            if(is_header && (item.created_date != m)) {
                
                comment_list += `
                    <div class="comment-feed-date${item.created_day == 'Today' ? ` today` : ``}">
                        <div class="w-bg">
                            <span class="day">${item.created_day}</span> 
                            ${item.created_date}
                        </div>
                    </div>`   
            }

            comment_list += `
            <div class="comment" data-id="${item.id}">
                <div class="comment--head">
                    <span class="--user">${item.user ? item.user.name : `troubl.io` }</span>
                    <span class="--at">${item.created_time}</span>
                    ${item.activity ? 
                        `<span class="--hours">${item.activity.hours_formatted}h <span>${item.activity.date_formatted}</span></span>` : ``
                    }
                    ${item.remind_at ? 
                        `<span class="--reminder">${item.remind_at_formatted}</span>` : ``
                    }
                    <div class="--actions">
                        ${myapp.templates.comment.actionButtons(item)}
                        <button type="button" class="reply" name="reply" data-tippy-content="Reply"><i class="material-icons">reply</i></button>
                    </div>
                </div>
                <div class="comment--body">${item.comment_html}</div>
                ${item.replies ? myapp.templates.comment.reply(item.replies) : ``}
            </div>`

            m = item.created_date
        }
        return comment_list.allTrim()
    },




    /**
     * Render a list of Reply or a single template
     * 
     * @param {Object} comments - Comments object array
     */
    reply(comments) {

        let reply_list = '' 

        comments.forEach(function(item) {

            reply_list += `
            <div class="comment--reply" data-id="${item.id}">
                <div class="comment--head">
                    <span class="--user">${item.user ? item.user.name : `troubl.io` }</span>
                    <span class="--at">${item.created_date}, ${item.created_time}</span>
                    <div class="--actions">
                        ${myapp.templates.comment.actionButtons(item)}
                    </div>
                </div>
                <div class="comment--body">${item.comment_html}</div>
            </div>`
        })
        return reply_list.allTrim()
    },




    /**
     * Add or Edit Comment editor
     * 
     * @param {obj} data - comment object
     * @param {str} data_type
     */
    renderEditor(data, data_type = '') {

        const template = `
            <div class="${data ? `edit` : `new`}">
                <input type="hidden" name="id" value="${data ? data.id : ``}"/>
                ${data.type == 2 ? `<input type="hidden" name="reply_comment_id" value=""/>` : ``}
                <div class="mde-editor">
                    <textarea class="comment" rows="1" name="comment" placeholder="Add a comment, hours or task">${data ? data.comment : ``}</textarea>
                    <button type="button" name="${data ? `edit` : `add`}" class="btn btn-primary btn-sm pull-right">${data ? `Save` : `Post`}</button>
                </div>
            </div>`

        return template.allTrim()
    },




    /**
     * Reply editor
     * 
     * @param {str} comment_id
     * @param {str} parent_comment_id
     */
    renderReplyEditor(comment_id, parent_comment_id) {

        const template = `
            <div class="edit reply">
                <input type="hidden" name="id" value="${comment_id}"/>
                <input type="hidden" name="parent_comment_id" value="${parent_comment_id}"/>
                <input type="hidden" name="notify" value="${comment_id > 0 ? 1 : 3}"/>
                <div class="mde-editor">
                    <textarea class="comment" rows="1" name="comment" placeholder="Add a comment"></textarea>
                    <button type="button" name="edit" class="btn btn-primary btn-sm pull-right">Post</button>
                </div>
            </div>`

        return template.allTrim()
    },



    /**
     * Render action buttons template
     * 
     * @param {Object} item - Comment object
     */
    actionButtons(item) {

        if(!item.can_edit) {
            return ''
        }

        let btn = 
            `<div class="btn-group">
                <button type="button" class="trigger" data-toggle="dropdown" aria-expanded="true">
                    <i class="material-icons">more_horiz</i>
                </button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li><button type="button" name="edit" value="${item.id}">Edit</button></li>
                    <li><button type="button" name="delete" value="${item.id}">Delete</button></li>
                </ul>
            </div>`
        
        return btn.allTrim()
    },


}