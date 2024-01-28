import process from 'node:process';
import chai from 'chai';
import chaiHttp from 'chai-http';
import chaiAlmost from 'chai-almost';

const {expect} = chai;

const BASE_URL = process.env.BASE_URL ?? 'http://localhost:3000';

chai.use(chaiHttp);
chai.use(chaiAlmost(0.1));

describe('End-to-end integration tests', () => {
	it('Do nothing', async function () {
		const res = await chai.request(BASE_URL).send();
		expect(res.status).to.equal(200);
	}).timeout(30_000);
});
