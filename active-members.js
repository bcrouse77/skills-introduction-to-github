class ActiveMembersTable extends FilterTable
{
	
	constructor(el, params = {})
	{
		super(el, '/portal/ajax/reports/active-members', params);
	}
	
	
	getHeader() 
	{
		return $(`<tr class='panel-heading'></tr>`)
			.append($('<th>Event ID</th>').click(() => this.sortBy('id')))
            .append($('<th>Location</th>').click(() => this.sortBy('location')))
            .append($('<th>Event Name</th>').click(() => this.sortBy('name')))
            .append($('<th>Time</th>').click(() => this.sortBy('startDate')))
            .append($('<th>Type</th>').click(() => this.sortBy('type')))
            .append($('<th>Attendance</th>').click(() => this.sortBy('attendance')));
	}
	
	
	createRow(lead)
	{
		return $(`<tr>
            <td>${lead['id']}</td>
            <td>${lead['location']}</td>
            <td>${lead['name']}</td>
            <td>${lead['startDate']} - ${lead['endDate']}</td>
            <td>${lead['type']}</td>
            <td>${lead['attendance']}</td>
        </tr>`).click(() => window.location.href=`/portal/AgentLeads/viewlead/${lead['leadId']}`)
	}
	
	
	getControls()
	{
		const typesEl = $('<select name="filterType" class="form-control-sm"></select>');
		
		typesEl.append('<option value="">All Types</option>');
		
		$.get('/portal/ajax/leads/types').promise().then(types => {
			console.log('here');
			types.forEach(t => {
				console.log("inhere?");
				if(t.name !== 'Tour' && t.name !== 'Membership')
				{
					typesEl.append($(`<option value="${t.id}">${t.name}</option>`))
				}
			})	
		})
		
		let toDate = new Date();
		toDate.setDate(toDate.getDate() + 14);
		
		return $('<div class="col-sm-8 row"></div>')
			.append(
				$('<div class="col-sm-3"></div>')
            )
            .append(
                $(`<div class="col-sm-4">
                    <label for="search">Search</label>
                    <input type="text" class="form-control-sm required" id="search" name="search" placeholder="Enter Search">
                </div>`).keyup(ev => this.searchFor(ev.target.value))
            )
	}
	searchFor(term)
	{
		if(term !== this.params.search)
			{
				this.params.search = term;
				this.params.page = 1;
				this.reloadTable();
			}
	}
}