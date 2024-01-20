import * as svc from '../services/task.js';
import express from 'express';
const router = express.Router();

//import NotFoundError from '../errors/not-found-error.js';

/**
 * @openapi
 * /v1/tasks:
 *   get:
 *     summary: Get all tasks.
 *     description: |
 *       Gets all the tasks queued and running
 *     produces:
 *       - application/json
 *     responses:
 *       '200':
 *         description: Success
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id:
 *                     type: number
 *                     description: The task number
 *                     example: 1
 *       default:
 *         description: All other errors
 *         content:
 *           application/json:
 *             schema:
 *               $ref: '#/components/schemas/Error'
 */
router.get('/v1/tasks', async function(_req, res) {
	const taskList = await svc.getAllTasks();
	res.set('cache-control', 'public, max-age=2419200').send(taskList);
});

export default router;
