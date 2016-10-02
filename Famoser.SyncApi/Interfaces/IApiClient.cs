using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiClient
    {
        Task<bool> EraseDataAsync();

        //Task<bool> Create(SyncEntity entity);
        //Task<bool> Read(SyncEntity entity);
        //Task<bool> Update(SyncEntity entity);
        //Task<bool> Delete(SyncEntity entity);
    }
}
