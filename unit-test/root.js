import chai from 'chai';
import chaiHttp from 'chai-http';
import {app,server} from '../src/js/index.js';

const {expect} = chai;

chai.use(chaiHttp);

describe('Root test', function() {
	after(() => server.close());
	
	it('Should get 404', async function() {
		const res = await chai.request(app).get('/').send();
		expect(res).to.have.status(404);
		expect(res.body).to.deep.include({
			type: '/errors/NOT_FOUND',
			title: 'Not Found',
			status: res.status,
			detail: 'GET / not a valid API.',
		});
		expect(res.body).to.have.property('instance');
	});
});
